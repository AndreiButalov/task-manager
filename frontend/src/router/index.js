import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import BoardView from '../views/BoardView.vue'
import TaskListView from '../views/TaskListView.vue'
import TaskView from '../views/TaskView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/boards',
      name: 'boards',
      component: BoardView,
    },
    {
      path: '/tasklists',
      name: 'taskList',
      component: TaskListView,
    },
    {
      path: '/task/:id',
      name: 'task',
      component: TaskView,
    },
  ],
})

export default router
