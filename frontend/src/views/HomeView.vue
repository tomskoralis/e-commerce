<template>
  <h1>Welcome</h1>
  <p v-if="store?.message">
    {{ store?.message }}
  </p>
</template>

<script lang="ts" setup>
import { inject } from "vue";
import type { Store } from "@/types/Store";

const store = inject("store") as Store;
</script>
