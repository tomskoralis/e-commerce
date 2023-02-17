<template>
  <div class="navigation-wrapper">
    <div class="navigation-container">
      <div v-if="store?.token" class="navigation">
        <nav v-show="visible || width > 767" class="navigation-links">
          <RouterLink class="link" to="/">Home</RouterLink>
          <RouterLink class="link" to="/products">Products</RouterLink>
          <RouterLink class="link" to="/products/new">New Product</RouterLink>
          <RouterLink class="link" to="/unavailable">Out of stock</RouterLink>
        </nav>
        <nav v-show="visible || width > 767" class="auth-navigation-links">
          <RouterLink class="link" to="/cart"
            >Cart{{ productCountInCart() }}</RouterLink
          >
          <RouterLink class="link" to="/account">Account</RouterLink>
          <button class="link" @click="logout">Logout</button>
        </nav>
      </div>
      <div v-else class="navigation">
        <nav v-show="visible || width > 767" class="navigation-links">
          <RouterLink class="link" to="/">Home</RouterLink>
        </nav>
        <nav v-show="visible || width > 767" class="auth-navigation-links">
          <RouterLink class="link" to="/register">Register</RouterLink>
          <RouterLink class="link" to="/login">Login</RouterLink>
        </nav>
      </div>
      <button @click="toggleNavigationExpand" class="hamburger">
        <Hamburger />
      </button>
    </div>
  </div>
</template>

<script lang="ts" setup>
import axios from "axios";
import { RouterLink, useRouter } from "vue-router";
import { inject, onMounted, ref } from "vue";
import type { Store } from "@/types/Store";
import Hamburger from "@/components/icons/HamburgerIcon.vue";

const store = inject("store") as Store;
const router = useRouter();
const url = "http://127.0.0.1:8000/api/v1/logout";
const width = ref(document.documentElement.clientWidth);
const visible = ref(false);

const toggleNavigationExpand = () => {
  visible.value = !visible.value;
};

onMounted(() => {
  window.addEventListener("resize", windowResize);
});

const windowResize = () => {
  width.value = document.documentElement.clientWidth;
};

const logout = async () => {
  try {
    const { data } = await axios.post(
      url,
      {},
      {
        headers: {
          Authorization: "Bearer " + store.token,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    );
    store.message = data.message;
  } catch (error) {
    if (axios.isAxiosError(error)) {
      store.message = error.response?.data?.message;
    }
  }
  sessionStorage.removeItem("token");
  store.token = "";
  store.cart = null;
  await router.push("/");
};

const productCountInCart = () => {
  if (store.cart && store.cart?.products.length > 0) {
    return `(${store.cart?.products.length})`;
  }
  return "";
};
</script>

<style lang="scss" scoped>
.navigation-wrapper {
  margin-bottom: 1rem;
  background-color: $secondary-background-color;

  .navigation-container {
    margin: 0 auto;
    min-width: 200px;
    max-width: 1024px;
  }
}

.navigation {
  margin: 0 1rem;
  display: flex;
  gap: 0.5rem;
  min-height: 3rem;
  justify-content: space-between;
  flex-direction: column;
  @include for-at-least($breakpoint-md) {
    align-items: center;
    flex-direction: row;
  }
}

.navigation-links {
  width: fit-content;
  display: flex;
  gap: 0.5rem;
  flex-direction: column;
  @include for-at-least($breakpoint-md) {
    align-items: center;
    flex-direction: row;
  }

  .link {
    width: max-content;
    height: 3rem;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    text-decoration: none;
    color: $background-color;
    background-color: $secondary-background-color;
    border: none;
    transition: ease-in-out 0.2s;

    &:hover {
      cursor: pointer;
      color: $link-hover-color;
      background-color: $background-color;
      box-shadow: 3px 3px $secondary-background-color;
      transform: translate(-3px, -3px);
    }

    &:active {
      background-color: $border-color;
    }
  }
}

.auth-navigation-links {
  @extend .navigation-links;
  justify-content: right;
}

.hamburger {
  @extend .button;
  width: 2.5rem;
  height: 2.5rem;
  padding: 0.2rem;
  display: block;
  position: absolute;
  top: 4px;
  right: 4px;
  @include for-at-least($breakpoint-md) {
    display: none;
  }
}
</style>
