import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import "./assets/base.css";
import { store } from "@/store/store";

const app = createApp(App);

app.provide("store", store);

app.use(router);

app.mount("#app");
