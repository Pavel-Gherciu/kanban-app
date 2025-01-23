<template>
  <div
    class="badge px-3 h-6 rounded-full text-xs font-semibold flex items-center"
    :style="{ backgroundColor: bgColor, color: textColor }"
  >
    <span
      class="dot w-2 h-2 rounded-full mr-1"
      :style="{ backgroundColor: dotColor }"
    ></span>
    <span>
      <slot></slot>
    </span>
  </div>
</template>

<script>
import Color from "color";

export default {
  name: "Badge",
  props: {
    color: {
      type: String,
      default: "#38B2AC",
    },
  },
  computed: {
    bgColor() {
      return Color(this.color).alpha(0.2).string();
    },
    textColor() {
      return Color(this.color).darken(0.5).string();
    },
    dotColor() {
      return this.color;
    },
  },
};
</script>

<style scoped></style>
