<template>
  <div
    class="board bg-gray-100 rounded-lg p-4 w-80 flex-shrink-0 relative"
    @mouseenter="showEdit = true"
    @mouseleave="showEdit = false"
    :data-board-id="board.id"
  >
    <div class="flex justify-between items-center mb-4">
      <div v-if="isEditingTitle" class="flex-grow">
        <input
          v-model="editableTitle"
          @blur="saveTitle"
          @keyup.enter="saveTitle"
          type="text"
          class="w-full p-1 border rounded focus:outline-none focus:ring focus:border-blue-300"
          required
        />
      </div>
      <h2 v-else class="text-lg font-bold truncate">
        {{ board.name }}
      </h2>
      <div v-if="showEdit && !isEditingTitle" class="flex space-x-2">
        <button
          @click="editTitle"
          class="text-blue-500 hover:text-blue-700"
          title="Edit Board"
        >
          <PencilIcon class="h-4 w-4" />
        </button>
        <button
          @click="confirmDeleteBoard"
          class="text-red-500 hover:text-red-700"
          title="Delete Board"
        >
          <TrashIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <Modal v-if="showDeleteModal" @close="closeDeleteModal" title="Delete Board">
      <template #body>
        <p>
          Are you sure you want to delete the board "<strong>{{ board.name }}</strong
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
          @click="deleteBoard"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
        >
          Delete
        </button>
      </template>
    </Modal>

    <draggable
      :list="board.tasks"
      @end="onTaskDragEnd"
      :group="{ name: 'tasks', pull: true, put: true }"
      :animation="200"
      ghost-class="ghost-card"
      class="tasks space-y-3"
    >
      <template #item="{ element, index, elementIndex, from, to, related }">
        <TaskCard
          :key="element.id"
          :task="element"
          :boardId="board.id"
          :projectId="projectId"
          @update-task="handleUpdateTask"
          @delete-task="handleDeleteTask"
        />
      </template>
    </draggable>

    <form @submit.prevent="addTask" class="mt-4">
      <input
        v-model="newTaskTitle"
        type="text"
        placeholder="Task Title"
        class="w-full p-2 border rounded mb-2"
        required
      />
      <input
        v-model="newTaskBadge"
        type="text"
        placeholder="Badge Label"
        class="w-full p-2 border rounded mb-2"
        required
      />
      <input
        v-model="newTaskColor"
        type="color"
        title="Choose Badge Color"
        class="w-full p-1 border rounded mb-2 cursor-pointer"
        required
      />
      <button
        type="submit"
        class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition-colors"
      >
        Add Task
      </button>
    </form>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import TaskCard from "./TaskCard.vue";
import Modal from "./Modal.vue";
import { useProjectStore } from "../store/kanban";
import { ref } from "vue";
import { PencilIcon, TrashIcon } from "@heroicons/vue/24/solid";

export default {
  name: "Board",
  components: {
    TaskCard,
    draggable,
    Modal,
    PencilIcon,
    TrashIcon,
  },
  props: {
    board: {
      type: Object,
      required: true,
    },
    projectId: {
      type: Number,
      required: true,
    },
  },
  setup(props, { emit }) {
    const projectStore = useProjectStore();

    const newTaskTitle = ref("");
    const newTaskBadge = ref("");
    const newTaskColor = ref("#38B2AC");
    const showEdit = ref(false);
    const isEditingTitle = ref(false);
    const editableTitle = ref(props.board.name);
    const showDeleteModal = ref(false);

    const addTask = async () => {
      if (newTaskTitle.value && newTaskBadge.value && newTaskColor.value) {
        const taskData = {
          title: newTaskTitle.value.trim(),
          type: newTaskBadge.value.trim(),
          color: newTaskColor.value,
          description: "",
          image_url: "",
          image_id: null,
          date: new Date().toISOString().split("T")[0],
        };
        try {
          await projectStore.addTask(props.projectId, props.board.id, taskData);
          newTaskTitle.value = "";
          newTaskBadge.value = "";
          newTaskColor.value = "#38B2AC";
        } catch (error) {
          console.error("Error adding task:", error);
        }
      }
    };

    const editTitle = () => {
      isEditingTitle.value = true;
      editableTitle.value = props.board.name;
    };

    const saveTitle = async () => {
      if (editableTitle.value.trim()) {
        try {
          await projectStore.updateBoard(
            props.projectId,
            props.board.id,
            editableTitle.value.trim()
          );
        } catch (error) {
          console.error("Error updating board title:", error);
        }
      }
      isEditingTitle.value = false;
    };

    const confirmDeleteBoard = () => {
      showDeleteModal.value = true;
    };

    const closeDeleteModal = () => {
      showDeleteModal.value = false;
    };

    const deleteBoard = async () => {
      try {
        await projectStore.deleteBoard(props.projectId, props.board.id);
        showDeleteModal.value = false;
      } catch (error) {
        console.error("Error deleting board:", error);
      }
    };

    const handleUpdateTask = async (payload) => {
      await projectStore.updateTask(
        payload.projectId,
        payload.boardId,
        payload.taskId,
        payload.updatedTask
      );
    };

    const handleDeleteTask = async (payload) => {
      await projectStore.deleteTask(payload.projectId, payload.boardId, payload.taskId);
    };

    const onTaskDragEnd = async (evt) => {
      const { moved, element, from, to } = evt;
      // If moved is falsy, it might mean the drag was cancelled or no change.
      if (!moved) return;

      // Identify which board we came from vs. which board we are going to.
      // Because <draggable> sets `evt.from` and `evt.to` to the inner
      // `.tasks` container, we climb up to the parent [data-board-id].
      const oldBoardId = Number(from.closest("[data-board-id]")?.dataset?.boardId);
      const newBoardId = Number(to.closest("[data-board-id]")?.dataset?.boardId);

      if (!oldBoardId || !newBoardId) {
        // Safety check: If we somehow don't get valid board IDs, just stop.
        return;
      }

      // If same board, it's a simple reorder. Just update positions in place.
      if (oldBoardId === newBoardId) {
        const updatedTasks = props.board.tasks.map((task, index) => ({
          id: task.id,
          position: index + 1,
          board_id: props.board.id,
        }));
        try {
          await projectStore.updateTaskPositions(
            props.projectId,
            props.board.id,
            updatedTasks
          );
        } catch (error) {
          console.error("Error reordering tasks in the same board:", error);
        }
      } else {
        // CROSS-BOARD MOVE
        try {
          // 1) Update the dragged task’s board_id in the backend.
          //    We call `updateTask` with oldBoardId to locate the task
          //    and set `board_id: newBoardId`.
          await projectStore.updateTask(
            props.projectId,
            oldBoardId, // old board
            element.id, // the moved task ID
            { board_id: newBoardId }
          );

          // 2) Re‐position tasks in the old board:
          const oldBoard = projectStore.getBoardById(props.projectId, oldBoardId);
          if (oldBoard) {
            const updatedOldBoardTasks = oldBoard.tasks.map((task, index) => ({
              id: task.id,
              position: index + 1,
              board_id: oldBoardId,
            }));
            await projectStore.updateTaskPositions(
              props.projectId,
              oldBoardId,
              updatedOldBoardTasks
            );
          }

          // 3) Re‐position tasks in the new board:
          const newBoard = projectStore.getBoardById(props.projectId, newBoardId);
          if (newBoard) {
            const updatedNewBoardTasks = newBoard.tasks.map((task, index) => ({
              id: task.id,
              position: index + 1,
              board_id: newBoardId,
            }));
            await projectStore.updateTaskPositions(
              props.projectId,
              newBoardId,
              updatedNewBoardTasks
            );
          }
        } catch (error) {
          console.error("Error moving task to another board:", error);
        }
      }
    };

    return {
      newTaskTitle,
      newTaskBadge,
      newTaskColor,
      showEdit,
      isEditingTitle,
      editableTitle,
      showDeleteModal,
      addTask,
      editTitle,
      saveTitle,
      confirmDeleteBoard,
      closeDeleteModal,
      deleteBoard,
      handleUpdateTask,
      handleDeleteTask,
      onTaskDragEnd,
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
</style>
