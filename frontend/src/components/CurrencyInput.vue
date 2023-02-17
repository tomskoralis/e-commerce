<template>
  <div class="input-wrapper">
    <label :for="props.id">{{ props.label }}</label>
    <div class="currency-input">
      <span class="currency-symbol">{{ props.currency }}</span>
      <input
        type="text"
        :id="props.id"
        :name="props.id"
        v-bind="$attrs"
        :value="inputValue"
        @input="updateValue"
        :placeholder="props.placeholder"
      />
    </div>
    <p v-if="error" class="error">{{ props.error }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  inheritAttrs: false,
});
</script>

<script lang="ts" setup>
interface Props {
  id: string;
  currency: string;
  inputValue: string;
  placeholder?: string;
  label?: string;
  error?: string;
}

interface Emits {
  (e: "update:inputValue", newValue: string): void;
}

const emit = defineEmits<Emits>();

const props = withDefaults(defineProps<Props>(), {
  placeholder: "0.00",
});

const updateValue = (e: Event) => {
  emit("update:inputValue", (e.target as HTMLInputElement).value);
};
</script>

<style lang="scss" scoped>
.input-wrapper {
  max-width: $breakpoint-sm;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  flex-grow: 1;
}

.currency-input {
  border: 1px solid $border-color;
  display: flex;
  align-items: center;
  padding-left: 0.5rem;
}

.currency-symbol {
  color: $link-color;
}

input {
  width: 100%;
  border: none;
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;

  &:focus {
    outline: none;
  }
}

.error {
  font-size: 0.75rem;
  line-height: 1rem;
  color: $error-color;
}
</style>
