// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";
import DefaultLayout from "./components/DefaultLayout.vue";
import Login from "./pages/Login.vue";
import Signup from "./pages/Signup.vue";
import NotFound from "./pages/NotFound.vue";
import AuthCallback from "./pages/AuthCallback.vue";
import ProjectList from "./pages/ProjectList.vue";
import ProjectDetail from "./pages/ProjectDetail.vue";
import useUserStore from "./store/user";

const routes = [
  {
    path: "/",
    component: DefaultLayout,
    children: [
      { path: "/", name: "Workspace", component: ProjectList },
      {
        path: "/project/:projectId",
        name: "ProjectDetail",
        component: ProjectDetail,
        props: true,
      },
    ],
    beforeEnter: async (to, from, next) => {
      try {
        const userStore = useUserStore();
        await userStore.fetchUser();
        next();
      } catch (error) {
        next(false);
      }
    },
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
  },
  {
    path: "/auth/callback",
    name: "AuthCallback",
    component: AuthCallback,
  },
  {
    path: "/signup",
    name: "Signup",
    component: Signup,
  },
  {
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
