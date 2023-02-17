<template>
  <h1 class="page-heading">Register</h1>

  <form @submit.prevent="handleSubmit" class="form">
    <TextInput
      id="name"
      label="Name"
      v-model:inputValue="state.name.value"
      :error="state.name.error"
    />

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

    <TextInput
      id="password-repeated"
      type="password"
      label="Repeat password"
      v-model:inputValue="state.passwordRepeated.value"
      :error="state.passwordRepeated.error"
    />

    <div class="button-container">
      <RouterLink :to="router.resolve({ name: 'Login' }).path"
        >Already registered? Login
      </RouterLink>
      <button type="submit" class="button">Register</button>
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
const url = "http://127.0.0.1:8000/api/v1/register";
const config = {
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
};

const state = reactive({
  name: {
    value: "",
    error: "",
  },
  email: {
    value: "",
    error: "",
  },
  password: {
    value: "",
    error: "",
  },
  passwordRepeated: {
    value: "",
    error: "",
  },
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

const isPasswordRepeatedValid = (): Boolean => {
  state.passwordRepeated.error = "";
  if (state.password.value && !state.passwordRepeated.value) {
    state.passwordRepeated.error = "Please repeat the password!";
    return false;
  }
  if (state.passwordRepeated.value !== state.password.value) {
    state.passwordRepeated.error = "The passwords must match!";
    return false;
  }
  return true;
};

const register = async () => {
  try {
    const params = {
      name: state.name.value,
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
      const err = error.response?.data?.errors;
      state.name.error = err.name?.[0];
      state.email.error = err.email?.[0];
      state.password.error = err.password?.[0];
    }
  }
};

const handleSubmit = () => {
  const nameValid = isNameValid();
  const emailValid = isEmailValid();
  const passwordValid = isPasswordValid();
  const passwordRepeatedValid = isPasswordRepeatedValid();
  if (!nameValid || !emailValid || !passwordValid || !passwordRepeatedValid) {
    return;
  }
  register();
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

.error-message {
  color: $error-color;
}
</style>
