<script setup>
import { RouterLink } from "vue-router";
import GuestLayout from "../components/GuestLayout.vue";
import axiosClient from "../axios";
import router from "../router";
import { ref } from "vue";

const data = ref({
  email: "",
  password: "",
});

const errorMessage = ref("");

function submit() {
  axiosClient.get("/sanctum/csrf-cookie").then(() => {
    axiosClient
      .post("/login", data.value)
      .then(() => {
        router.push({ name: "Workspace" });
      })
      .catch((error) => {
        console.log(error.response);
        errorMessage.value = error.response.data.message;
      });
  });
}

function loginWith(provider) {
  // Redirect to the backend's OAuth provider route
  const baseUrl = import.meta.env.VITE_API_BASE_URL;
  window.location.href = `${baseUrl}/auth/redirect/${provider}`;
}
</script>

<template>
  <GuestLayout>
    <h2 class="mt-10 text-center text-2xl font-bold tracking-tight text-gray-900">
      Sign in to your account
    </h2>
    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-900">
            Email address
          </label>
          <div class="mt-2">
            <input
              type="email"
              name="email"
              id="email"
              autocomplete="email"
              required
              v-model="data.email"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            />
          </div>
        </div>
        <p v-if="errorMessage" class="text-sm mt-1 text-red-600">
          {{ errorMessage }}
        </p>
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-900">
              Password
            </label>
            <div class="text-sm">
              <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">
                Forgot password?
              </a>
            </div>
          </div>
          <div class="mt-2">
            <input
              type="password"
              name="password"
              id="password"
              autocomplete="current-password"
              required
              v-model="data.password"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            Sign in
          </button>
        </div>
      </form>

      <div class="mt-6">
        <div class="flex items-center">
          <div class="flex-grow h-px bg-gray-300"></div>
          <div class="px-4 text-sm text-gray-500">or</div>
          <div class="flex-grow h-px bg-gray-300"></div>
        </div>
        <div class="mt-4 flex flex-col space-y-4">
          <button
            @click="loginWith('google')"
            class="flex items-center justify-center w-full rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
          >
            Continue with Google
          </button>
          <button
            @click="loginWith('github')"
            class="flex items-center justify-center w-full rounded-md bg-gray-800 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-800"
          >
            Continue with GitHub
          </button>
        </div>
      </div>

      <p class="mt-10 text-center text-sm text-gray-500">
        Not a member?
        <RouterLink
          :to="{ name: 'Signup' }"
          class="font-semibold text-indigo-600 hover:text-indigo-500"
        >
          Create an Account
        </RouterLink>
      </p>
    </div>
  </GuestLayout>
</template>

<style scoped></style>
