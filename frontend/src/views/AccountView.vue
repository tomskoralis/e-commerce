<template>
  <div v-if="state.isLoading" class="loader"></div>
  <p v-else-if="state.isError" class="error-message">
    {{ state.errorMessage }}
  </p>

  <div v-else class="account">
    <div>
      <h1>Account</h1>
      <p v-if="state.balance !== ''">Balance: €{{ state.balance }}</p>
    </div>

    <form @submit.prevent="handleSubmit" class="form">
      <h3>Add Money</h3>

      <CurrencyInput
        id="amount"
        label=""
        :currency="'€'"
        v-model:inputValue="state.amount.value"
        :error="state.amount.error"
      />

      <div class="button-container">
        <button type="submit" class="button">Confirm</button>
      </div>
    </form>
  </div>

  <table v-if="state?.orders && state?.orders.length > 0" class="orders">
    <caption>
      Orders
    </caption>
    <thead>
      <tr>
        <th class="name-column">Name</th>
        <th>Price</th>
        <th>Vat</th>
        <th>Count</th>
        <th>Bought at</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="product in state?.orders" :key="product.id">
        <th class="name-column">{{ product.name }}</th>
        <th>€{{ product.price }}</th>
        <th>€{{ product.vat }}</th>
        <th>{{ product.count }}</th>
        <th>{{ product.boughtAt }} UTC</th>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts" setup>
import axios from "axios";
import { inject, onMounted, reactive } from "vue";
import type { Store } from "@/types/Store";
import type { FetchedProduct } from "@/types/FetchedProduct";
import type { Product } from "@/types/Product";
import CurrencyInput from "@/components/CurrencyInput.vue";

const store = inject("store") as Store;

const urlBalance = "http://127.0.0.1:8000/api/v1/balance";
const urlOrders = "http://127.0.0.1:8000/api/v1/orders";

const state = reactive({
  balance: "",
  amount: {
    value: "",
    error: "",
  },
  isLoading: false,
  isError: false,
  errorMessage: "",
  orders: null as Product[] | null,
});

const fetchBalance = async () => {
  try {
    state.isLoading = true;
    const { data } = await axios.get(urlBalance, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
      },
    });
    state.balance = data.balance;
    state.isLoading = false;
  } catch (error) {
    state.isLoading = false;
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const isAmountValid = (): Boolean => {
  state.amount.error = "";
  if (!state.amount.value) {
    state.amount.error = "The amount is required!";
    return false;
  }
  if (Number(state.amount.value) < 0.01) {
    state.amount.error = "The amount must be at least €0.01!";
    return false;
  }
  if (Number(state.amount.value) + Number(state.balance) > 4294967295.99) {
    state.amount.error = "The balance cannot be more than €4294967295.99!";
    return false;
  }
  return true;
};

const handleSubmit = () => {
  if (!isAmountValid()) {
    return;
  }
  addBalance();
};

const addBalance = async () => {
  try {
    const { data } = await axios.put(
      urlBalance,
      {
        amount: state.amount.value,
      },
      {
        headers: {
          Authorization: "Bearer " + store.token,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    );
    state.balance = data.balance;
    state.amount.value = "";
  } catch (error) {
    if (axios.isAxiosError(error)) {
      state.amount.error = error.response?.data?.errors.amount?.[0];
    }
  }
};

const fetchOrders = async () => {
  try {
    state.isLoading = true;
    const { data } = await axios.get(urlOrders, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
      },
    });
    const products = data.orders as FetchedProduct[];
    state.orders = products.map((product: FetchedProduct): Product => {
      return {
        id: product.id,
        name: product.name,
        available: product.available,
        price: product.price,
        vatRate: product.vat_rate,
        vat: product.vat,
        count: product.count,
        boughtAt: product.bought_at,
      };
    });
    state.isLoading = false;
  } catch (error) {
    state.isLoading = false;
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

onMounted(() => {
  fetchBalance();
  fetchOrders();
});
</script>

<style lang="scss" scoped>
@import "../assets/loader";

.account {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1rem;
  @include for-at-least($breakpoint-sm) {
    flex-direction: row;
  }
}

.form {
  max-width: 10rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.button-container {
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: end;
}

.orders {
  border-spacing: 0;
  text-align: right;
  word-break: keep-all;
  margin-top: 1rem;
  @include for-at-least($breakpoint-sm) {
    margin-top: 0;
  }

  caption {
    font-size: 1.5rem;
    line-height: 2rem;
  }

  thead,
  tbody {
    font-size: 0.75rem;
    line-height: 1rem;
    @include for-at-least($breakpoint-sm) {
      font-size: 1rem;
      line-height: 1.5rem;
    }
    tr {
      th {
        padding: 0.1rem;
        @include for-at-least($breakpoint-sm) {
          padding: 0.2rem 0.5rem;
        }
      }
      .name-column {
        text-align: left;
      }
    }
  }
  tbody {
    tr {
      &:hover {
        background-color: $border-color;
      }
      th {
        border-top: 1px solid $border-color;
      }
    }
  }
}

.error-message {
  color: $error-color;
}
</style>
