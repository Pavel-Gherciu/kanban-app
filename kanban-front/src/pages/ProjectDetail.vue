<template>
  <div class="kanban-board p-6 bg-gray-200 min-h-screen">
    <header>
      <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex justify-between items-center"
      >
        <h1 v-if="project" class="text-3xl font-bold tracking-tight text-gray-900">
          {{ project.name }}
        </h1>
        <h1 v-else class="text-3xl font-bold tracking-tight text-gray-900">Loading...</h1>
        <div class="flex items-center space-x-4">
          <button
            @click="toggleLayout"
            class="flex items-center px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100"
            :disabled="loading"
          >
            <Bars3Icon class="w-5 h-5 mr-2" />
            Toggle Layout
          </button>
        </div>
      </div>
    </header>

    <div class="mx-auto flex flex-col mt-4">
      <div v-if="project" :class="boardContainerClass" class="gap-4 items-start">
        <draggable
          v-model="project.boards"
          @end="onBoardDragEnd"
          :group="{ name: 'boards', pull: 'clone', put: false }"
          :animation="200"
          ghost-class="ghost-board"
          class="flex gap-4 items-start draggable-container"
        >
          <template #item="{ element }">
            <Board
              :key="element.id"
              :board="element"
              :projectId="project.id"
              data-board-id="element.id"
              @add-task="handleAddTask"
              @delete-board="handleDeleteBoard"
              @update-task="handleUpdateTask"
              @delete-task="handleDeleteTask"
            />
          </template>
        </draggable>

        <div
          v-if="!showAddBoard"
          @click="showAddBoard = true"
          class="board bg-gray-100 rounded-lg p-4 w-80 flex-shrink-0 flex flex-col justify-center items-center cursor-pointer border-2 border-dashed border-gray-400"
        >
          <span class="text-4xl">+</span>
          <span class="mt-2 text-gray-600">Add Board</span>
        </div>
      </div>

      <div v-else-if="loading" class="flex justify-center items-center mt-10">
        <span class="text-xl text-gray-700">Loading project...</span>
      </div>

      <div v-else class="flex justify-center items-center mt-10">
        <span class="text-xl text-red-500">Project not found.</span>
      </div>

      <Modal v-if="showAddBoard" @close="closeAddBoard" title="Add New Board">
        <template #body>
          <form @submit.prevent="addBoard">
            <input
              v-model="newBoardName"
              type="text"
              placeholder="Board Name"
              class="w-full p-2 border rounded mb-4"
              required
            />
            <div class="flex justify-end">
              <button
                type="button"
                @click="closeAddBoard"
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
        </template>
      </Modal>
    </div>
  </div>
</template>

<script>
import { useProjectStore } from "../store/kanban";
import { computed, ref, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import Board from "../components/Board.vue";
import draggable from "vuedraggable";
import Modal from "../components/Modal.vue";
import { Bars3Icon } from "@heroicons/vue/24/solid";

export default {
  name: "ProjectDetail",
  components: {
    Board,
    draggable,
    Modal,
    Bars3Icon,
  },
  setup() {
    const route = useRoute();
    const projectStore = useProjectStore();
    const projectId = parseInt(route.params.projectId);

    const project = computed(() => projectStore.projects.find((p) => p.id === projectId));
    const loading = ref(true);
    const showAddBoard = ref(false);
    const newBoardName = ref("");
    const horizontalLayout = ref(false);

    const boardContainerClass = computed(() =>
      horizontalLayout.value ? "flex flex-nowrap overflow-x-auto" : "flex flex-wrap"
    );

    const toggleLayout = () => {
      horizontalLayout.value = !horizontalLayout.value;
    };

    const addBoard = async () => {
      if (newBoardName.value.trim()) {
        await projectStore.addBoard(projectId, newBoardName.value);
        newBoardName.value = "";
        showAddBoard.value = false;
      }
    };

    const closeAddBoard = () => {
      showAddBoard.value = false;
      newBoardName.value = "";
    };

    const handleAddTask = async (boardId, task) => {
      await projectStore.addTask(projectId, boardId, task);
    };

    const handleDeleteBoard = async (boardId) => {
      await projectStore.deleteBoard(projectId, boardId);
    };

    const handleUpdateTask = async ({ projectId: pid, boardId, taskId, updatedTask }) => {
      await projectStore.updateTask(pid, boardId, taskId, updatedTask);
    };

    const handleDeleteTask = async ({ projectId: pid, boardId, taskId }) => {
      await projectStore.deleteTask(pid, boardId, taskId);
    };

    const onBoardDragEnd = async () => {
      if (!project.value) return;

      const updatedBoards = project.value.boards.map((board, index) => ({
        id: board.id,
        position: index + 1,
      }));

      await projectStore.updateBoardPositions(projectId, updatedBoards);
    };

    onMounted(async () => {
      try {
        await projectStore.fetchProjects();
        if (project.value) {
          project.value.boards.sort((a, b) => a.position - b.position);
        }
        loading.value = false;
      } catch (error) {
        console.error("Failed to fetch projects:", error);
        loading.value = false;
      }
    });

    watch(
      () => projectStore.projects,
      (newProjects) => {
        if (project.value) {
          loading.value = false;
        }
      },
      { immediate: true }
    );

    return {
      project,
      loading,
      showAddBoard,
      newBoardName,
      horizontalLayout,
      boardContainerClass,
      toggleLayout,
      addBoard,
      closeAddBoard,
      handleAddTask,
      handleDeleteBoard,
      handleUpdateTask,
      handleDeleteTask,
      onBoardDragEnd,
    };
  },
};
</script>

<style scoped>
.ghost-card {
  opacity: 0.5;
  background: #f7fafc;
  border: 1px solid #4299e1;
}
.ghost-board {
  opacity: 0.5;
  background: #e2e8f0;
  border: 1px solid #cbd5e0;
}
.draggable-container {
  display: contents;
}
</style>
