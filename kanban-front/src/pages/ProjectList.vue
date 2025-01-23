<template>
  <div class="project-list p-6 bg-gray-200 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Your Projects</h1>
    </div>

    <div v-if="loading" class="text-center text-gray-600">Loading projects...</div>

    <div v-if="error" class="text-center text-red-500">
      {{ error }}
    </div>

    <div v-if="!loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <div
        v-for="project in projects"
        :key="project.id"
        class="block bg-white shadow rounded p-4 hover:bg-gray-100 cursor-pointer relative"
      >
        <div @click="goToProject(project.id)">
          <h2 class="text-xl font-semibold">{{ project.name }}</h2>
          <p class="text-gray-600 mt-2">{{ project.boards.length }} Boards</p>
        </div>

        <div class="absolute top-2 right-2 flex space-x-2">
          <button
            @click.stop="openEditModal(project)"
            class="text-blue-500 hover:text-blue-700"
            title="Edit Project"
          >
            <PencilIcon class="h-5 w-5" />
          </button>
          <button
            @click.stop="openDeleteModal(project)"
            class="text-red-500 hover:text-red-700"
            title="Delete Project"
          >
            <TrashIcon class="h-5 w-5" />
          </button>
        </div>
      </div>
      <div
        @click="showAddProject = true"
        class="flex flex-col items-center justify-center bg-gray-100 shadow rounded p-4 hover:bg-gray-200 cursor-pointer"
      >
        <span class="text-4xl">+</span>
        <span class="mt-2 text-gray-600">Add New Project</span>
      </div>
    </div>

    <div
      v-if="showAddProject"
      class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center"
    >
      <div class="bg-white rounded-lg p-6 w-80">
        <h2 class="text-xl font-bold mb-4">Add New Project</h2>
        <form @submit.prevent="addProject">
          <input
            v-model="newProjectName"
            type="text"
            placeholder="Project Name"
            class="w-full p-2 border rounded mb-4"
            required
          />
          <div class="flex justify-end">
            <button
              type="button"
              @click="showAddProject = false"
              class="mr-2 px-4 py-2 border rounded hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600"
            >
              Add
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center"
    >
      <div class="bg-white rounded-lg p-6 w-80">
        <h2 class="text-xl font-bold mb-4">Edit Project</h2>
        <form @submit.prevent="saveProjectName">
          <input
            v-model="editedProjectName"
            type="text"
            placeholder="Project Name"
            class="w-full p-2 border rounded mb-4"
            required
          />
          <div class="flex justify-end">
            <button
              type="button"
              @click="closeEditModal"
              class="mr-2 px-4 py-2 border rounded hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center"
    >
      <div class="bg-white rounded-lg p-6 w-80">
        <h2 class="text-xl font-bold mb-4">Delete Project</h2>
        <p class="mb-4">
          Are you sure you want to delete the project "{{ projectToDelete?.name }}"? This
          action cannot be undone.
        </p>
        <div class="flex justify-end">
          <button
            type="button"
            @click="closeDeleteModal"
            class="mr-2 px-4 py-2 border rounded hover:bg-gray-100"
          >
            Cancel
          </button>
          <button
            @click="deleteProjectConfirmed"
            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useProjectStore } from "../store/kanban";
import { computed, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { PencilIcon, TrashIcon } from "@heroicons/vue/24/solid";

export default {
  name: "ProjectList",
  components: {
    PencilIcon,
    TrashIcon,
  },
  setup() {
    const projectStore = useProjectStore();
    const projects = computed(() => projectStore.projects);
    const loading = computed(() => projectStore.loading);
    const error = computed(() => projectStore.error);
    const showAddProject = ref(false);
    const newProjectName = ref("");
    const router = useRouter();

    const showEditModal = ref(false);
    const projectToEdit = ref(null);
    const editedProjectName = ref("");

    const showDeleteModal = ref(false);
    const projectToDelete = ref(null);

    onMounted(() => {
      projectStore.fetchProjects();
    });

    const addProject = async () => {
      if (newProjectName.value.trim()) {
        await projectStore.addProject(newProjectName.value);
        newProjectName.value = "";
        showAddProject.value = false;
      }
    };

    const goToProject = (projectId) => {
      router.push(`/project/${projectId}`);
    };

    const openEditModal = (project) => {
      projectToEdit.value = project;
      editedProjectName.value = project.name;
      showEditModal.value = true;
    };

    const closeEditModal = () => {
      showEditModal.value = false;
      projectToEdit.value = null;
      editedProjectName.value = "";
    };

    const saveProjectName = async () => {
      if (editedProjectName.value.trim()) {
        await projectStore.updateProject(projectToEdit.value.id, editedProjectName.value);
        closeEditModal();
      }
    };

    const openDeleteModal = (project) => {
      projectToDelete.value = project;
      showDeleteModal.value = true;
    };

    const closeDeleteModal = () => {
      showDeleteModal.value = false;
      projectToDelete.value = null;
    };

    const deleteProjectConfirmed = async () => {
      if (projectToDelete.value) {
        await projectStore.deleteProject(projectToDelete.value.id);
        closeDeleteModal();
      }
    };

    return {
      projects,
      loading,
      error,
      showAddProject,
      newProjectName,
      addProject,
      goToProject,
      showEditModal,
      openEditModal,
      closeEditModal,
      editedProjectName,
      saveProjectName,
      showDeleteModal,
      openDeleteModal,
      closeDeleteModal,
      projectToDelete,
      deleteProjectConfirmed,
    };
  },
};
</script>

<style scoped></style>
