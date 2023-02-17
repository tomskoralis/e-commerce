<template>
  <h1 class="page-heading">{{ state.product?.name ?? "Product" }}</h1>

  <div v-if="state.isLoading" class="loader"></div>
  <p v-else-if="state.isError" class="error-message">
    {{ state.errorMessage }}
  </p>

  <div v-else class="product">
    <p>Price: €{{ state.product?.price }}</p>
    <p>VAT rate: {{ state.product?.vatRate }}%</p>
    <p>VAT: €{{ state.product?.vat }}</p>
    <p>Available: {{ state.product?.available }}</p>

    <div class="button-container">
      <button type="button" @click="router.back()" class="button">
        Go back
      </button>

      <div class="button-container">
        <button
          v-if="state.product?.available > 0"
          class="button in-corner"
          @click="addProductToCart"
        >
          Add to cart
        </button>

        <RouterLink class="link" :to="route.fullPath + '/edit'">
          <button v-if="state.product" class="button">Edit</button>
        </RouterLink>

        <button
          v-if="state.product"
          @click="deleteProduct()"
          class="danger-button"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import type { Product } from "@/types/Product";
import type { FetchedProduct } from "@/types/FetchedProduct";
import type { Store } from "@/types/Store";

const store = inject("store") as Store;

const route = useRoute();
const router = useRouter();
const urlProducts = "http://127.0.0.1:8000/api/v1/products";
const urlCart = "http://127.0.0.1:8000/api/v1/cart";
const id = route.params.id;

const state = reactive({
  product: null as Product | null,
  message: "",
  isLoading: false,
  isError: false,
  errorMessage: "",
});

const fetchSingleProduct = async () => {
  try {
    state.isLoading = true;
    const { data } = await axios.get(`${urlProducts}/${id}`, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
      },
    });
    state.product = {
      id: data.product.id,
      name: data.product.name,
      available: data.product.available,
      price: data.product.price,
      vatRate: data.product.vat_rate,
      vat: data.product.vat,
    };
    state.isLoading = false;
  } catch (error) {
    state.isLoading = false;
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const deleteProduct = async () => {
  try {
    await axios.delete(`${urlProducts}/${id}`, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
      },
    });
    router.back();
  } catch (error) {
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const addProductToCart = async () => {
  try {
    const { data } = await axios.post(
      urlCart,
      {
        id: id,
      },
      {
        headers: {
          Authorization: "Bearer " + store.token,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    );
    const products = data.cart.products as FetchedProduct[];
    store.cart = {
      products: products.map((product: FetchedProduct): Product => {
        return {
          id: product.id,
          name: product.name,
          available: product.available,
          price: product.price,
          vatRate: product.vat_rate,
          vat: product.vat,
        };
      }),
      subtotal: data.cart.subtotal,
      vat: data.cart.vat,
      total: data.cart.total,
    };
  } catch (error) {
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

onMounted(() => {
  fetchSingleProduct();
});
</script>

<style lang="scss" scoped>
@import "../assets/loader";

.product {
  margin: 0.5rem auto 0;
  max-width: $breakpoint-sm;
}

.link {
  text-decoration: none;
}

.button-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.error-message {
  color: $error-color;
}
</style>
