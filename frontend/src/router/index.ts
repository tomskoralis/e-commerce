import { createRouter, createWebHistory } from "vue-router";
import { store } from "@/store/store";
import HomeView from "@/views/HomeView.vue";
import RegisterView from "@/views/RegisterView.vue";
import LoginView from "@/views/LoginView.vue";
import ProductsView from "@/views/ProductsView.vue";
import ProductNewView from "@/views/ProductNewView.vue";
import ProductEditView from "@/views/ProductEditView.vue";
import CartView from "@/views/CartView.vue";
import ProductView from "@/views/ProductView.vue";
import AccountView from "@/views/AccountView.vue";
import OutOfStockView from "@/views/OutOfStockView.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "Home",
      component: HomeView,
    },
    {
      path: "/register",
      name: "Register",
      component: RegisterView,
    },
    {
      path: "/login",
      name: "Login",
      component: LoginView,
    },
    {
      path: "/products",
      name: "Products",
      component: ProductsView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: "/products/:id",
      name: "Product",
      component: ProductView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: "/products/:id/edit",
      name: "Edit Product",
      component: ProductEditView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: "/products/new",
      name: "New Product",
      component: ProductNewView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: "/unavailable",
      name: "Out of stock",
      component: OutOfStockView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: "/cart",
      name: "Cart",
      component: CartView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: "/account",
      name: "Account",
      component: AccountView,
      meta: {
        requiresAuth: true,
      },
    },
  ],
});

router.beforeEach((to, from, next) => {
  document.title = String(to.name) + " | Shop";
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!store.token) {
      next({ name: "Login" });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
