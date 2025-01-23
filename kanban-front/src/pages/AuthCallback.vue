<script setup>
import router from "../router";
import { onMounted } from "vue";
import axiosClient from "../axios";

// Automatically fetch user data after OAuth callback
onMounted(async () => {
  try {
    await axiosClient.get("/api/user"); // Fetch user data
    router.push({ name: "Workspace" }); // Redirect to home page
  } catch (error) {
    console.error("Authentication failed", error);
    router.push({ name: "Login" }); // Redirect back to login
  }
});
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <p class="text-gray-600 text-lg">Authenticating...</p>
  </div>
</template>

<style scoped></style>
