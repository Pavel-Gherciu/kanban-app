<script setup>
import { ref } from "vue";
import axiosClient from "../axios";
import { PhotoIcon } from "@heroicons/vue/24/solid";

const emits = defineEmits(["image-uploaded"]);

const data = ref({
  image: null,
  label: "",
});

const uploading = ref(false);
const uploadProgress = ref(0);
const error = ref("");
const fileAdded = ref(false);
const fileName = ref("");
const uploadSuccess = ref(false);

function handleFileChange(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type and size
  if (!file.type.startsWith("image/")) {
    error.value = "Please upload a valid image file.";
    fileAdded.value = false;
    fileName.value = "";
    return;
  }
  if (file.size > 50 * 1024 * 1024) {
    error.value = "Image size should be less than 50MB.";
    fileAdded.value = false;
    fileName.value = "";
    return;
  }

  data.value.image = file;
  error.value = "";
  fileAdded.value = true;
  fileName.value = file.name;
  uploadSuccess.value = false;
}

function handleDrop(event) {
  event.preventDefault();
  const file = event.dataTransfer.files[0];
  if (file) handleFileChange({ target: { files: [file] } });
}

function handleDragOver(event) {
  event.preventDefault();
}

async function submit() {
  if (!data.value.image || !data.value.label.trim()) {
    error.value = "Both image and label are required.";
    return;
  }

  uploading.value = true;
  uploadProgress.value = 0;
  const formData = new FormData();
  formData.append("image", data.value.image);
  formData.append("label", data.value.label);

  try {
    const response = await axiosClient.post("/api/image", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round(
            (progressEvent.loaded / progressEvent.total) * 100
          );
        }
      },
    });

    emits("image-uploaded", {
      id: response.data.id,
      url: response.data.url,
      label: response.data.label,
    });
  } catch (err) {
    console.error(err);
    error.value = "Failed to upload image.";
  } finally {
    uploading.value = false;
    uploadProgress.value = 0;
  }
}
</script>

<template>
  <main>
    <div class="mx-auto">
      <form @submit.prevent="submit">
        <div class="mb-3">
          <label for="cover-photo" class="block text-sm font-medium text-gray-900">
            Image
          </label>
          <div
            class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 py-6"
            @drop="handleDrop"
            @dragover="handleDragOver"
          >
            <div class="text-center">
              <PhotoIcon class="mx-auto h-8 w-8 text-gray-300" aria-hidden="true" />
              <div class="mt-3 flex text-sm text-gray-600">
                <label
                  for="file-upload"
                  class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-600"
                >
                  <span>Upload a file</span>
                  <input
                    id="file-upload"
                    name="file-upload"
                    type="file"
                    accept="image/*"
                    @change="handleFileChange"
                    class="sr-only"
                  />
                </label>
                <p class="pl-1">or drag and drop</p>
              </div>
              <p class="text-xs text-gray-600">PNG, JPG, GIF up to 10MB</p>
            </div>
          </div>
          <div v-if="fileAdded" class="mt-2 text-sm text-green-500">
            File added: {{ fileName }}
          </div>
          <div v-if="uploading" class="mt-2 text-sm text-gray-500">
            Uploading... {{ uploadProgress }}%
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
              <div
                class="bg-indigo-600 h-2.5 rounded-full"
                :style="{ width: `${uploadProgress}%` }"
              ></div>
            </div>
          </div>
          <div v-if="uploadSuccess" class="mt-2 text-sm text-green-500">
            File {{ fileName }} uploaded successfully!
          </div>
          <div v-if="error" class="mt-2 text-sm text-red-500">{{ error }}</div>
        </div>
        <div class="mb-3">
          <label for="label" class="block text-sm font-medium text-gray-900">Label</label>
          <div class="mt-2">
            <input
              v-model="data.label"
              type="text"
              id="label"
              class="block w-full rounded-md bg-white px-3 py-1 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
              placeholder="Enter label"
            />
          </div>
        </div>
        <button
          type="submit"
          class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
          Upload
        </button>
      </form>
    </div>
  </main>
</template>

<style scoped></style>
