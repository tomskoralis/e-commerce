<template>
  <h1 class="page-heading">New Product</h1>

  <form @submit.prevent="handleSubmit" action="" class="form">
    <TextInput
      id="name"
      label="Name"
      v-model:inputValue="state.name.value"
      :error="state.name.error"
    />
    <TextInput
      id="available"
      label="Available amount"
      placeholder="0"
      v-model:inputValue="state.available.value"
      :error="state.available.error"
    />
    <CurrencyInput
      id="price"
      label="Price"
      :currency="'€'"
      v-model:inputValue="state.price.value"
      :error="state.price.error"
    />
    <TextInput
      id="vat_rate"
      label="Value Added Tax rate"
      placeholder="21"
      v-model:inputValue="state.vatRate.value"
      :error="state.vatRate.error"
    />

    <div class="button-container">
      <button type="button" class="button" @click="router.back()">
        Go back
      </button>
      <p v-if="state.message" class="flash-message">{{ state.message }}</p>
      <button type="submit" class="button">Submit</button>
    </div>
  </form>
</template>

<script lang="ts" setup>
import axios from "axios";
import { inject, reactive } from "vue";
import { useRouter } from "vue-router";
import type { Store } from "@/types/Store";
import TextInput from "../components/TextInput.vue";
import CurrencyInput from "@/components/CurrencyInput.vue";

const store = inject("store") as Store;

const router = useRouter();
const url = "http://127.0.0.1:8000/api/v1/products";

const state = reactive({
  name: {
    value: "",
    error: "",
  },
  available: {
    value: "",
    error: "",
  },
  price: {
    value: "",
    error: "",
  },
  vatRate: {
    value: "21",
    error: "",
  },
  message: "",
});

const isNameValid = (): Boolean => {
  state.name.error = "";
  if (!state.name.value) {
    state.name.error = "The name is required!";
    return false;
  }
  if (state.name.value.length > 255) {
    state.name.error = "The name cannot be more than 255 characters!";
    return false;
  }
  return true;
};

const isAvailableValid = (): Boolean => {
  state.available.error = "";
  if (!state.available.value) {
    state.available.error = "The available amount is required!";
    return false;
  }
  if (Number(state.available.value) < 1) {
    state.available.error = "The available amount must be at least 1!";
    return false;
  }
  if (Number(state.available.value) > 4294967295) {
    state.available.error =
      "The available amount cannot be more than 4294967295!";
    return false;
  }
  return true;
};

const isPriceValid = (): Boolean => {
  state.price.error = "";
  if (!state.price.value) {
    state.price.error = "The price is required!";
    return false;
  }
  if (Number(state.price.value) < 0.01) {
    state.price.error = "The price must be at least €0.01!";
    return false;
  }
  if (Number(state.price.value) > 4294967295.99) {
    state.price.error = "The price cannot be more than €4294967295.99!";
    return false;
  }
  return true;
};

const isVatValid = (): Boolean => {
  state.vatRate.error = "";
  if (!state.vatRate.value) {
    state.vatRate.error = "The VAT rate is required!";
    return false;
  }
  if (Number(state.vatRate.value) < 0) {
    state.vatRate.error = "The VAT rate must be at least 0!";
    return false;
  }
  if (Number(state.vatRate.value) > 99.99) {
    state.vatRate.error = "The VAT rate cannot be more than 99.99!";
    return false;
  }
  return true;
};

const sendProduct = async () => {
  try {
    const params = {
      name: state.name.value,
      available: state.available.value,
      price: state.price.value,
      vat_rate: state.vatRate.value,
    };
    const { data } = await axios.post(`${url}`, params, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });

    state.name.value = "";
    state.available.value = "";
    state.price.value = "";
    state.vatRate.value = "21";
    state.message =
      "Successfully added " + data.product.name + " to the available products.";
  } catch (error) {
    if (axios.isAxiosError(error)) {
      const err = error.response?.data?.errors;
      state.name.error = err.name?.[0];
      state.available.error = err.available?.[0];
      state.price.error = err.price?.[0];
      state.vatRate.error = err.vat_rate?.[0];
      state.message = "";
    }
  }
};

const handleSubmit = () => {
  const nameValid = isNameValid();
  const availableValid = isAvailableValid();
  const priceValid = isPriceValid();
  const vatValid = isVatValid();
  if (!nameValid || !availableValid || !priceValid || !vatValid) {
    return;
  }
  sendProduct();
};
</script>

<style lang="scss" scoped>
.form {
  margin: 0 auto;
  max-width: $breakpoint-sm;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.button-container {
  margin-top: 0.25rem;
  display: flex;
  align-items: start;
  justify-content: space-between;
}

.flash-message {
  padding: 0 1rem;
  font-size: 0.75rem;
  line-height: 1rem;
  color: $success-color;
}
</style>
