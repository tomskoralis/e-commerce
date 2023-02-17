<template>
  <div
    class="product-card"
    @mouseover="hover = true"
    @mouseleave="hover = false"
  >
    <div class="product-info">
      <router-link :to="`/products/${props.id}`">
        <h2 v-if="props.name">{{ props.name }}</h2>
      </router-link>
      <p v-if="props.price">€{{ props.price }}</p>
    </div>
    <Transition>
      <button
        v-if="cart && hover"
        @click="$emit('addToCart', props.id)"
        class="button in-corner"
      >
        Add to cart
      </button>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

interface Product {
  id: number;
  name: string;
  price: string;
  cart?: boolean;
}

const hover = ref(false);

const props = withDefaults(defineProps<Product>(), {
  cart: true,
});
</script>

<style scoped lang="scss">
.product-info {
  padding: 0.2rem;
  row-gap: 0.2rem;
  display: flex;
  flex-wrap: wrap;
  flex-direction: column;
  justify-content: center;
  @include for-at-least($breakpoint-sm) {
    padding: 0.8rem;
  }

  p {
    max-height: 3em;
    overflow: hidden;
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }
}

.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

.in-corner {
  position: absolute;
  right: 5px;
  bottom: 5px;
}
</style>
