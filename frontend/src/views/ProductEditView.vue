<template>
  <h1 class="page-heading">
    Edit
    <span v-if="state.previousName"> {{ state.previousName }}</span>
    <span v-else>Product</span>
  </h1>

  <div v-if="state.isLoading" class="loader"></div>
  <p v-else-if="state.isError" class="error-message">
    {{ state.errorMessage }}
  </p>

  <form v-else @submit.prevent="handleSubmit" action="" class="form">
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
import { inject, onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import type { Store } from "@/types/Store";
import TextInput from "../components/TextInput.vue";
import CurrencyInput from "@/components/CurrencyInput.vue";

const store = inject("store") as Store;

const route = useRoute();
const router = useRouter();
const url = "http://127.0.0.1:8000/api/v1/products";
const id = route.params.id;

const state = reactive({
  previousName: "",
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
  isLoading: false,
  isError: false,
  errorMessage: "",
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

const fetchSingleProduct = async () => {
  state.isLoading = true;
  try {
    const { data } = await axios.get(`${url}/${id}`, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
    state.previousName = data.product.name;
    state.name.value = data.product.name;
    state.available.value = String(data.product.available);
    state.price.value = data.product.price;
    state.vatRate.value = data.product.vat_rate;
    state.isLoading = false;
  } catch (error) {
    state.isLoading = false;
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const sendProduct = async () => {
  try {
    const params = {
      name: state.name.value,
      available: state.available.value,
      price: state.price.value,
      vat_rate: state.vatRate.value,
    };
    const { data } = await axios.put(`${url}/${id}`, params, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
    state.message = "Successfully edited " + state.previousName + ".";
    state.previousName = data.product.name;
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

onMounted(() => {
  fetchSingleProduct();
});
</script>

<style lang="scss" scoped>
@import "../assets/loader";

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

.error-message {
  color: $error-color;
}
</style>
