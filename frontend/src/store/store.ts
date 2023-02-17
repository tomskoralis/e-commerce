import type { Store } from "@/types/Store";
import { reactive } from "vue";

const sessionToken = sessionStorage.getItem("token");

export const store: Store = reactive({
  token: sessionToken ?? "",
  message: "",
  cart: null,
});
