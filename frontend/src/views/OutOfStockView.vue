<template>
  <h1 class="page-heading">Products out of stock</h1>

  <div v-if="state.isLoading" class="loader"></div>
  <p v-else-if="state.isError" class="error-message">
    {{ state.errorMessage }}
  </p>

  <div v-else>
    <p>Page {{ page }} of {{ meta.pageCount }}</p>
    <p v-if="meta.to > 0">
      Displaying products {{ meta.from }} - {{ meta.to }}
    </p>
    <p v-else class="error-message">No products found!</p>

    <ul class="product-list">
      <li
        v-for="product in state.products"
        :key="product.id"
        class="card-container"
      >
        <ProductCard
          :id="product.id"
          :name="product.name"
          :price="product.price"
          :cart="false"
        />
      </li>
    </ul>
    <div class="button-wrapper">
      <ul class="long-button-row">
        <li v-for="i in meta.pageCount" :key="i">
          <button
            class="page-button"
            @click="handleNewPage(i)"
            :class="{ 'current-page': i === page }"
            v-if="
              i === 1 ||
              i === meta.pageCount ||
              (i >= page - 2 && i <= page + 2)
            "
          >
            {{ i }}
          </button>
          <button
            class="page-between"
            @click.prevent
            v-if="
              (i !== 1 && i === page - 3) ||
              (i === page + 3 && i !== meta.pageCount)
            "
          >
            ...
          </button>
        </li>
      </ul>

      <ul class="short-button-row">
        <li>
          <button
            :disabled="page <= 1"
            @click="handleNewPage(page - 1)"
            class="page-button"
          >
            &#60;
          </button>
        </li>
        <li>
          <div class="current-page">
            {{ page }}
          </div>
        </li>
        <li>
          <button
            :disabled="page >= meta.pageCount"
            @click="handleNewPage(page + 1)"
            class="page-button"
          >
            &#62;
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import ProductCard from "@/components/ProductCard.vue";
import type { Product } from "@/types/Product";
import type { Store } from "@/types/Store";

const store = inject("store") as Store;

const route = useRoute();
const router = useRouter();
const urlProducts = "http://127.0.0.1:8000/api/v1/unavailable";

const state = reactive({
  products: [] as Product[],
  isLoading: false,
  isError: false,
  errorMessage: "",
});

const meta = reactive({
  pageCount: 0,
  from: 0,
  to: 0,
  productCount: 0,
});

const page = ref(Number(route.query.page) || 1);

const fetchProducts = async (page: number) => {
  try {
    state.isLoading = true;
    const { data } = await axios.get(`${urlProducts}?page=${page}`, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
    state.products = data.products.map((product: Product) => {
      return {
        id: product.id,
        name: product.name,
        price: product.price,
      };
    });
    meta.pageCount = data.meta.last_page;
    meta.from = data.meta.from;
    meta.to = data.meta.to;
    meta.productCount = data.meta.total;
    state.isLoading = false;
  } catch (error) {
    state.isLoading = false;
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const handleNewPage = (newPage: number) => {
  page.value = newPage;
  fetchProducts(page.value);
  router.push({
    query: {
      page: page.value,
    },
  });
};

onMounted(() => {
  fetchProducts(page.value);
});
</script>

<style scoped lang="scss">
@import "../assets/loader";

.product-list {
  margin-top: 1rem;
  display: grid;
  gap: 1rem;
  @include for-at-least($breakpoint-md) {
    grid-template-columns: repeat(2, 1fr);
  }
}

.card-container .product-card {
  color: $secondary-background-color;
  border: 2px solid $border-color;
  border-radius: 0.3rem;
  display: flex;
  gap: 0.5rem;
  flex-direction: column;
  transition: ease-in-out 0.2s;
  text-decoration: none;
  @include for-at-least($breakpoint-sm) {
    max-width: 100%;
    flex-direction: row;
  }
  @include for-at-least($breakpoint-md) {
    height: 100%;
  }

  &:hover {
    transform: scale(104%);
  }
}

.button-wrapper {
  width: fit-content;
  margin: 1rem auto 0;
}

.long-button-row {
  display: none;
  flex-direction: row;
  width: fit-content;
  @include for-at-least($breakpoint-md) {
    display: flex;
  }
}

.short-button-row {
  display: flex;
  margin-bottom: 1rem;
  @extend .long-button-row;
  @include for-at-least($breakpoint-md) {
    display: none;
  }
}

.page-button {
  min-width: 2rem;
  height: 1.9rem;
  margin: 0 0.25rem;
  background-color: $background-color;
  border: 2px solid $border-color;
  border-radius: 0.5rem;
  padding: 0.1rem 0.7rem;
  text-align: center;
  font-size: 0.9rem;
  cursor: pointer;
  touch-action: manipulation;
  white-space: nowrap;

  &:hover {
    border: 2px solid $link-hover-color;
  }

  &:disabled {
    border: none;
    cursor: not-allowed;
  }
}

.page-between {
  @extend .page-button;
  cursor: default;

  &:hover {
    border: 2px solid $border-color;
  }
}

.current-page {
  @extend .page-button;
  border: 2px solid $secondary-background-color;

  &:hover {
    border: 2px solid $secondary-background-color;
  }
}

.error-message {
  color: $error-color;
}
</style>
