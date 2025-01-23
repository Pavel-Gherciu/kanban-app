// src/store/kanban.js
import { defineStore } from "pinia";
import axiosClient from "../axios.js";
import { ref } from "vue";

export const useProjectStore = defineStore("projectStore", {
  state: () => ({
    projects: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchProjects() {
      this.loading = true;
      try {
        const response = await axiosClient.get("/api/projects");
        this.projects = response.data.map(project => ({
          ...project,
          boards: project.boards || [],
        }));
        this.error = null;
      } catch (error) {
        console.error("Failed to fetch projects:", error);
        this.error = "Failed to fetch projects.";
      } finally {
        this.loading = false;
      }
    },

    async addProject(name) {
      try {
        const response = await axiosClient.post("/api/projects", { name });
        this.projects.push({ ...response.data, boards: response.data.boards || [] });
        this.error = null;
      } catch (error) {
        console.error("Failed to add project:", error);
        this.error = "Failed to add project.";
      }
    },

    async updateProject(id, newName) {
      try {
        const response = await axiosClient.put(`/api/projects/${id}`, { name: newName });
        const index = this.projects.findIndex((p) => p.id === id);
        if (index !== -1) {
          this.projects[index] = {
            ...response.data,
            boards: response.data.boards || this.projects[index].boards || [],
          };
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to update project:", error);
        this.error = "Failed to update project.";
      }
    },

    async deleteProject(id) {
      try {
        await axiosClient.delete(`/api/projects/${id}`);
        this.projects = this.projects.filter((p) => p.id !== id);
        this.error = null;
      } catch (error) {
        console.error("Failed to delete project:", error);
        this.error = "Failed to delete project.";
      }
    },

    async fetchBoards(projectId) {
      try {
        const response = await axiosClient.get(`/api/projects/${projectId}/boards`);
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          project.boards = response.data;
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to fetch boards:", error);
        this.error = "Failed to fetch boards.";
      }
    },

    async addBoard(projectId, boardName) {
      try {
        const response = await axiosClient.post(`/api/projects/${projectId}/boards`, { name: boardName });
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          project.boards.push(response.data);
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to add board:", error);
        this.error = "Failed to add board.";
      }
    },

    getBoardById(projectId, boardId) {
      const project = this.projects.find((p) => p.id === projectId);
      if (!project) return null;
      return project.boards.find((b) => b.id === boardId);
    },
    
    async updateBoard(projectId, boardId, newName) {
      try {
        const response = await axiosClient.put(`/api/projects/${projectId}/boards/${boardId}`, { name: newName });
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          const boardIndex = project.boards.findIndex((b) => b.id === boardId);
          if (boardIndex !== -1) {
            project.boards[boardIndex] = response.data;
          }
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to update board:", error);
        this.error = "Failed to update board.";
      }
    },

    async deleteBoard(projectId, boardId) {
      try {
        await axiosClient.delete(`/api/projects/${projectId}/boards/${boardId}`);
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          project.boards = project.boards.filter((b) => b.id !== boardId);
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to delete board:", error);
        this.error = "Failed to delete board.";
      }
    },

    async fetchTasks(projectId, boardId) {
      try {
        const response = await axiosClient.get(`/api/projects/${projectId}/boards/${boardId}/tasks`);
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          const board = project.boards.find((b) => b.id === boardId);
          if (board) {
            board.tasks = response.data;
          }
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to fetch tasks:", error);
        this.error = "Failed to fetch tasks.";
      }
    },

    async addTask(projectId, boardId, taskData) {
      try {
        const response = await axiosClient.post(`/api/projects/${projectId}/boards/${boardId}/tasks`, taskData);
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          const board = project.boards.find((b) => b.id === boardId);
          if (board) {
            if (!board.tasks) {
              board.tasks = [];
            }
            board.tasks.push(response.data);
          }
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to add task:", error);
        this.error = "Failed to add task.";
      }
    },

    async updateTask(projectId, boardId, taskId, updatedTask) {
      try {
        const response = await axiosClient.put(`/api/projects/${projectId}/boards/${boardId}/tasks/${taskId}`, updatedTask);
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          const board = project.boards.find((b) => b.id === boardId);
          if (board) {
            const taskIndex = board.tasks.findIndex((t) => t.id === taskId);
            if (taskIndex !== -1) {
              board.tasks[taskIndex] = response.data;
            }
          }
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to update task:", error);
        this.error = "Failed to update task.";
      }
    },

    async deleteTask(projectId, boardId, taskId) {
      try {
        await axiosClient.delete(`/api/projects/${projectId}/boards/${boardId}/tasks/${taskId}`);
        const project = this.projects.find((p) => p.id === projectId);
        if (project) {
          const board = project.boards.find((b) => b.id === boardId);
          if (board) {
            board.tasks = board.tasks.filter((t) => t.id !== taskId);
          }
        }
        this.error = null;
      } catch (error) {
        console.error("Failed to delete task:", error);
        this.error = "Failed to delete task.";
      }
    },

    async updateBoardPositions(projectId, boards) {
      try {
        await axiosClient.put(`/api/projects/${projectId}/boards/positions`, { boards });
        this.error = null
      } catch (error) {
        console.error("Failed to update board positions:", error);
        this.error = "Failed to update board positions.";
      }
    },

    async updateTaskPositions(projectId, boardId, tasks) {
      try {
        await axiosClient.put(`/api/projects/${projectId}/boards/${boardId}/tasks/positions`, { tasks });
        const project = this.projects.find(p => p.id === projectId);
        if (project) {
          const board = project.boards.find(b => b.id === boardId);
          if (board) {
            tasks.forEach(taskData => {
              const task = board.tasks.find(t => t.id === taskData.id);
              if (task) {
                task.position = taskData.position;
              }
            });
          }
        }
        this.error = null;
      } catch (error) {
        console.error('Failed to update task positions:', error);
        this.error = 'Failed to update task positions.';
      }
    },
  }
});
