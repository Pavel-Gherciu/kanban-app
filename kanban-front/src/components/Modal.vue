<template>
  <Teleport to="#modals">
    <div
      class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto p-6 relative"
        @click.stop
      >
        <button
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
          @click="close"
          aria-label="Close Modal"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>

        <header v-if="title" class="mb-4">
          <h2 class="text-xl font-semibold">{{ title }}</h2>
        </header>

        <section class="mb-4">
          <slot name="body"></slot>
        </section>

        <footer class="flex justify-end space-x-2">
          <slot name="footer"> </slot>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { XMarkIcon } from "@heroicons/vue/24/solid";

export default {
  name: "Modal",
  components: {
    XMarkIcon,
  },
  props: {
    title: {
      type: String,
      default: "",
    },
  },
  emits: ["close"],
  methods: {
    close() {
      this.$emit("close");
    },
  },
};
</script>

<style scoped></style>
