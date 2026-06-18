import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import BoardView from '../views/BoardView.vue'
import BoardSingleView from '../views/BoardSingleView.vue'
import TaskListView from '../views/TaskListView.vue'
import TaskView from '../views/TaskView.vue'
import { isAuthenticated } from '../services/auth'

const routes = [
  {
    path: '/',
    redirect: () => {
      return isAuthenticated() ? { name: 'dashboard' } : { name: 'login' }
    },
  },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/boards', name: 'boards', component: BoardView, meta: { requiresAuth: true } },
  { path: '/boards/:id', name: 'boardSingle', component: BoardSingleView, meta: { requiresAuth: true } },
  { path: '/tasklists', name: 'taskList', component: TaskListView, meta: { requiresAuth: true } },
  { path: '/task/:id', name: 'task', component: TaskView, meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !isAuthenticated()) {
    return { name: 'login' }
  }

  if ((to.name === 'login' || to.name === 'register') && isAuthenticated()) {
    return { name: 'dashboard' }
  }
})

export default router
