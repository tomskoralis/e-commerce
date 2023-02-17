<template>
  <h1 class="page-heading">Login</h1>

  <form @submit.prevent="handleSubmit" class="form">
    <TextInput
      id="email"
      label="E-mail"
      v-model:inputValue="state.email.value"
      :error="state.email.error"
    />

    <TextInput
      id="password"
      type="password"
      label="Password"
      v-model:inputValue="state.password.value"
      :error="state.password.error"
    />

    <div class="button-container">
      <RouterLink :to="router.resolve({ name: 'Register' }).path"
        >Register</RouterLink
      >
      <button type="submit" class="button">Login</button>
    </div>
  </form>
</template>

<script lang="ts" setup>
import axios from "axios";
import { inject, reactive } from "vue";
import { useRouter } from "vue-router";
import TextInput from "../components/TextInput.vue";
import type { Store } from "@/types/Store";

const store = inject("store") as Store;

const router = useRouter();
const url = "http://127.0.0.1:8000/api/v1/login";
const config = {
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
};

const state = reactive({
  email: {
    value: "",
    error: "",
  },
  password: {
    value: "",
    error: "",
  },
});

const isEmailValid = (): Boolean => {
  state.email.error = "";
  if (!state.email.value) {
    state.email.error = "The email is required!";
    return false;
  }
  const urlRegex = new RegExp(/^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/);
  if (!urlRegex.test(state.email.value)) {
    state.email.error = "The email must be a valid email address.";
    return false;
  }
  return true;
};

const isPasswordValid = (): Boolean => {
  state.password.error = "";
  if (!state.password.value) {
    state.password.error = "The password is required!";
    return false;
  }
  if (state.password.value.length > 255) {
    state.password.error = "The password cannot be more than 255 characters!";
    return false;
  }
  return true;
};

const login = async () => {
  try {
    const params = {
      email: state.email.value,
      password: state.password.value,
    };
    const { data } = await axios.post(url, params, config);
    sessionStorage.setItem("token", data.token);
    store.token = data.token;
    store.message = data.message;
    await router.push("/");
  } catch (error) {
    if (axios.isAxiosError(error)) {
      state.password.error = error.response?.data?.message;
    }
  }
};

const handleSubmit = () => {
  const emailValid = isEmailValid();
  const passwordValid = isPasswordValid();
  if (!emailValid || !passwordValid) {
    return;
  }
  login();
};
</script>

<style lang="scss" scoped>
.form {
  margin: 0 auto;
  max-width: $breakpoint-sm;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.button-container {
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
