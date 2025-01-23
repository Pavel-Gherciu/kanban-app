<template>
  <div
    class="task-card bg-white shadow rounded px-4 py-3 border border-white relative transition-all duration-300 hover:shadow-lg hover:border-gray-300 cursor-pointer"
    @mouseenter="showEdit = true"
    @mouseleave="showEdit = false"
  >
    <div class="flex justify-between items-center">
      <p class="text-gray-700 font-semibold text-sm">{{ task.title }}</p>
      <div v-if="showEdit" class="flex space-x-2">
        <button
          @click.stop="openEditModal"
          class="text-blue-500 hover:text-blue-700"
          title="Edit Task"
        >
          <PencilIcon class="h-4 w-4" />
        </button>

        <button
          @click.stop="openDeleteModal"
          class="text-red-500 hover:text-red-700"
          title="Delete Task"
        >
          <TrashIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <div v-if="task.image_url" class="mt-2">
      <img
        :src="task.image_url"
        alt="Task Image"
        class="w-full h-auto object-contain rounded"
      />
    </div>

    <div class="flex justify-between items-center mt-2">
      <span class="text-sm text-gray-600">{{ task.date }}</span>
      <Badge :color="task.color">{{ task.type }}</Badge>
    </div>

    <Modal v-if="isEditModalOpen" @close="closeEditModal" title="Edit Task">
      <template #body>
        <form @submit.prevent="saveEdit">
          <div class="mb-4">
            <label for="taskTitle" class="block text-sm font-medium text-gray-700">
              Title
            </label>
            <input
              id="taskTitle"
              v-model="editableTitle"
              type="text"
              class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              required
            />
          </div>

          <div class="mb-4">
            <label for="taskBadge" class="block text-sm font-medium text-gray-700">
              Badge Label
            </label>
            <input
              id="taskBadge"
              v-model="editableBadge"
              type="text"
              class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              required
            />
          </div>

          <div class="mb-4">
            <label for="taskColor" class="block text-sm font-medium text-gray-700">
              Badge Color
            </label>
            <input
              id="taskColor"
              v-model="editableColor"
              type="color"
              class="w-full p-1 border rounded cursor-pointer"
              required
            />
          </div>

          <div class="mb-4">
            <label for="taskDescription" class="block text-sm font-medium text-gray-700">
              Description
            </label>
            <textarea
              id="taskDescription"
              v-model="editableDescription"
              class="w-full p-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
              rows="4"
              placeholder="Enter task description..."
            ></textarea>
          </div>

          <ImageUploader
            :initialImageUrl="editableImageUrl"
            @image-uploaded="handleImageUploaded"
            @image-removed="handleImageRemoved"
          />

          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 border rounded hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600"
            >
              Save
            </button>
          </div>
        </form>
      </template>
    </Modal>

    <Modal v-if="isDeleteModalOpen" @close="closeDeleteModal" title="Delete Task">
      <template #body>
        <p>
          Are you sure you want to delete the task "<strong>{{ task.title }}</strong
          >"? This action cannot be undone.
        </p>
      </template>
      <template #footer>
        <button
          @click="closeDeleteModal"
          class="px-4 py-2 border rounded hover:bg-gray-100"
        >
          Cancel
        </button>
        <button
          @click="deleteTask"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
        >
          Delete
        </button>
      </template>
    </Modal>
  </div>
</template>

<script>
import Badge from "./Badge.vue";
import Modal from "./Modal.vue";
import ImageUploader from "./ImageUploader.vue";
import { useProjectStore } from "../store/kanban";
import { ref } from "vue";
import { PencilIcon, TrashIcon } from "@heroicons/vue/24/solid";

export default {
  name: "TaskCard",
  components: {
    Badge,
    Modal,
    ImageUploader,
    PencilIcon,
    TrashIcon,
  },
  props: {
    task: {
      type: Object,
      required: true,
    },
    boardId: {
      type: Number,
      required: true,
    },
    projectId: {
      type: Number,
      required: true,
    },
  },
  setup(props, { emit }) {
    const projectStore = useProjectStore();

    const showEdit = ref(false);
    const isEditModalOpen = ref(false);
    const isDeleteModalOpen = ref(false);
    const editableTitle = ref(props.task.title);
    const editableBadge = ref(props.task.type);
    const editableColor = ref(props.task.color);
    const editableDescription = ref(props.task.description || "");
    const editableImageUrl = ref(props.task.image_url || "");
    const editableImageId = ref(props.task.image_id || null);

    const openEditModal = () => {
      isEditModalOpen.value = true;
      editableTitle.value = props.task.title;
      editableBadge.value = props.task.type;
      editableColor.value = props.task.color;
      editableDescription.value = props.task.description || "";
      editableImageUrl.value = props.task.image_url || "";
      editableImageId.value = props.task.image_id || null;
    };

    const closeEditModal = () => {
      isEditModalOpen.value = false;
    };

    const saveEdit = async () => {
      if (editableTitle.value.trim() && editableBadge.value && editableColor.value) {
        const updatedTask = {
          title: editableTitle.value.trim(),
          type: editableBadge.value.trim(),
          color: editableColor.value,
          description: editableDescription.value.trim(),
          image_url: editableImageUrl.value || null,
          image_id: editableImageId.value || null,
          date: props.task.date,
        };
        const payload = {
          projectId: props.projectId,
          boardId: props.boardId,
          taskId: props.task.id,
          updatedTask,
        };
        await projectStore.updateTask(
          payload.projectId,
          payload.boardId,
          payload.taskId,
          payload.updatedTask
        );
        isEditModalOpen.value = false;
      }
    };

    const openDeleteModal = () => {
      isDeleteModalOpen.value = true;
    };

    const closeDeleteModal = () => {
      isDeleteModalOpen.value = false;
    };

    const deleteTask = async () => {
      const payload = {
        projectId: props.projectId,
        boardId: props.boardId,
        taskId: props.task.id,
      };
      await projectStore.deleteTask(payload.projectId, payload.boardId, payload.taskId);
      isDeleteModalOpen.value = false;
    };

    const handleImageUploaded = (image) => {
      editableImageUrl.value = image.url;
      editableImageId.value = image.id;
    };

    const handleImageRemoved = () => {
      editableImageUrl.value = "";
      editableImageId.value = null;
    };

    return {
      showEdit,
      isEditModalOpen,
      isDeleteModalOpen,
      editableTitle,
      editableBadge,
      editableColor,
      editableDescription,
      editableImageUrl,
      editableImageId,
      openEditModal,
      closeEditModal,
      saveEdit,
      openDeleteModal,
      closeDeleteModal,
      deleteTask,
      handleImageUploaded,
      handleImageRemoved,
    };
  },
};
</script>

<style scoped>
.task-card {
  transition: all 0.3s ease-in-out;
}
.task-card:hover {
  transform: scale(1.03);
}
</style>
