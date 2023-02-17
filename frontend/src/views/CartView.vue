<template>
  <h1>Cart</h1>

  <div v-if="state.isLoading" class="loader"></div>
  <p v-else-if="state.isError" class="error-message">
    {{ state.errorMessage }}
  </p>

  <div v-else-if="store?.cart && store?.cart.products.length > 0">
    <table class="products">
      <thead>
        <tr>
          <th class="name-column">Name</th>
          <th>Price</th>
          <th>Vat</th>
          <th>Count</th>
          <th>Available</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in store?.cart.products" :key="product.id">
          <th class="name-column">{{ product.name }}</th>
          <th>€{{ product.price }}</th>
          <th>€{{ product.vat }}</th>
          <th>{{ product.count }}</th>
          <th>{{ product.available }}</th>
          <th>
            <button
              @click="removeProductFromCart(product.id)"
              class="danger-button"
            >
              Remove
            </button>
          </th>
        </tr>
      </tbody>
    </table>

    <div class="cart-details">
      <p>Subtotal: €{{ store?.cart.subtotal }}</p>
      <p>VAT: €{{ store?.cart.vat }}</p>
      <p>Total: €{{ store?.cart.total }}</p>
      <button @click="checkout" class="button checkout">Checkout</button>
    </div>
  </div>
  <p v-else>The cart is empty.</p>

  <p v-if="state.message">{{ state.message }}</p>
</template>

<script lang="ts" setup>
import axios from "axios";
import { inject, onMounted, reactive } from "vue";
import type { FetchedProduct } from "@/types/FetchedProduct";
import type { Product } from "@/types/Product";
import type { Store } from "@/types/Store";
import type { FetchedCart } from "@/types/FetchedCart";

const store = inject("store") as Store;

const urlCart = "http://127.0.0.1:8000/api/v1/cart";
const urlCheckout = "http://127.0.0.1:8000/api/v1/checkout";

const state = reactive({
  message: "",
  isLoading: false,
  isError: false,
  errorMessage: "",
});

const fetchCart = async () => {
  try {
    state.isLoading = true;
    const { data } = await axios.get(urlCart, {
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
    saveCartInStore(data.cart);
    state.isLoading = false;
  } catch (error) {
    state.isLoading = false;
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const removeProductFromCart = async (id: number) => {
  try {
    const { data } = await axios.delete(urlCart, {
      data: {
        id: id,
      },
      headers: {
        Authorization: "Bearer " + store.token,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });
    saveCartInStore(data.cart);
  } catch (error) {
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

const saveCartInStore = (cart: FetchedCart) => {
  const products = cart.products as FetchedProduct[];
  store.cart = {
    products: products.map((product: FetchedProduct): Product => {
      return {
        id: product.id,
        name: product.name,
        available: product.available,
        price: product.price,
        vatRate: product.vat_rate,
        vat: product.vat,
        count: product.count,
      };
    }),
    subtotal: cart.subtotal,
    vat: cart.vat,
    total: cart.total,
  };
};

const checkout = async () => {
  try {
    const { data } = await axios.post(
      urlCheckout,
      {},
      {
        headers: {
          Authorization: "Bearer " + store.token,
          Accept: "application/json",
        },
      }
    );
    state.message = data.message;
    store.cart = null;
  } catch (error) {
    state.isError = true;
    if (axios.isAxiosError(error)) {
      state.errorMessage = error.response?.data.message;
    }
  }
};

onMounted(() => {
  fetchCart();
});
</script>

<style lang="scss" scoped>
@import "../assets/loader";

.error {
  font-size: 0.75rem;
  line-height: 1rem;
  color: $error-color;
}

.products {
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
        .danger-button {
          font-size: 0.7rem;
          line-height: 0.9rem;
          padding: 0.25rem 0.5rem;
          @include for-at-least($breakpoint-sm) {
            font-size: 0.75rem;
            line-height: 1rem;
            padding: 0.3rem 0.8rem;
          }
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

.cart-details,
.checkout {
  margin-top: 0.5rem;
}

.error-message {
  color: $error-color;
}
</style>
