<template>
  <Navigation />

  <div class="main-wrapper">
    <div class="main-container">
      <RouterView />
    </div>
  </div>

  <div class="footer-wrapper">
    <footer class="footer">Made by Toms Korālis</footer>
  </div>
</template>

<script lang="ts" setup>
import Navigation from "@/components/NavigationBar.vue";
import { RouterView } from "vue-router";
</script>

<style lang="scss" scoped>
.main-wrapper {
  margin: 0 1rem;

  .main-container {
    min-width: 200px;
    max-width: 800px;
    margin: 0 auto;
    word-break: break-word;

    @include for-at-least($breakpoint-md) {
      padding: 0 1rem 1rem 1rem;
    }
  }
}

.footer-wrapper {
  padding: 0 1rem;
  background-color: $secondary-background-color;
  min-height: 2rem;
  margin-top: auto;

  .footer {
    margin: 0 auto;
    padding: 0.5rem;
    color: $background-color;
    width: fit-content;
  }
}
</style>
