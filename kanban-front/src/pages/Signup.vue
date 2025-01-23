<script setup>
import { RouterLink } from "vue-router";
import GuestLayout from "../components/GuestLayout.vue";
import axiosClient from "../axios";
import router from "../router";
import { ref } from "vue";

const data = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const errors = ref({
  name: [],
  email: [],
  password: [],
});

function submit() {
  axiosClient.get("/sanctum/csrf-cookie").then(() => {
    axiosClient
      .post("/register", data.value)
      .then(() => {
        router.push({ name: "Workspace" });
      })
      .catch((error) => {
        console.log(error.response);
        errors.value = error.response.data.errors;
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
      Create new account
    </h2>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-900"
            >Full Name</label
          >
          <div class="mt-2">
            <input
              name="name"
              id="name"
              v-model="data.name"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 sm:text-sm"
            />
            <p class="text-sm mt-1 text-red-600">
              {{ errors.name ? errors.name[0] : "" }}
            </p>
          </div>
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-900"
            >Email address</label
          >
          <div class="mt-2">
            <input
              type="email"
              name="email"
              id="email"
              autocomplete="email"
              v-model="data.email"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 sm:text-sm"
            />
            <p class="text-sm mt-1 text-red-600">
              {{ errors.email ? errors.email[0] : "" }}
            </p>
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-900"
            >Password</label
          >
          <div class="mt-2">
            <input
              type="password"
              name="password"
              id="password"
              v-model="data.password"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 sm:text-sm"
            />
            <p class="text-sm mt-1 text-red-600">
              {{ errors.password ? errors.password[0] : "" }}
            </p>
          </div>
        </div>

        <div>
          <label
            for="passwordConfirmation"
            class="block text-sm font-medium text-gray-900"
            >Confirm Password</label
          >
          <div class="mt-2">
            <input
              type="password"
              name="password_confirmation"
              id="passwordConfirmation"
              v-model="data.password_confirmation"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline outline-1 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 sm:text-sm"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600"
          >
            Create an Account
          </button>
        </div>
      </form>

      <div class="flex items-center mt-6">
        <div class="flex-grow h-px bg-gray-300"></div>
        <div class="px-4 text-sm text-gray-500">or</div>
        <div class="flex-grow h-px bg-gray-300"></div>
      </div>

      <div class="mt-4 space-y-4">
        <button
          @click="loginWith('google')"
          class="w-full flex items-center justify-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-500"
        >
          Continue with Google
        </button>
        <button
          @click="loginWith('github')"
          class="w-full flex items-center justify-center rounded-md bg-gray-800 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700"
        >
          Continue with GitHub
        </button>
      </div>

      <p class="mt-10 text-center text-sm text-gray-500">
        Already have an account?
        <RouterLink
          :to="{ name: 'Login' }"
          class="font-semibold text-indigo-600 hover:text-indigo-500"
        >
          Login now
        </RouterLink>
      </p>
    </div>
  </GuestLayout>
</template>

<style scoped></style>
