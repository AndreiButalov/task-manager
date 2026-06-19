<script setup>
import { useRouter } from 'vue-router'
import { getUser, logout } from '../services/auth'
import { loadBoards, loadBoard } from '../services/boardService';
import { ref, computed, onMounted } from "vue";

const router = useRouter()
const user = ref(getUser());
const boards = ref([]);
const stats = ref({ total: 0, todo: 0, inProgress: 0, done: 0 });

async function refreshBoards() {
  boards.value = await loadBoards();
  await loadTaskStats();
}

async function loadTaskStats() {
  const loadedBoards = await Promise.all(boards.value.map(board => loadBoard(board.id)));
  const allTasks = loadedBoards.flatMap(board => board.taskLists?.flatMap(list => list.tasks || []) || []);

  stats.value.total = allTasks.length;
  stats.value.todo = allTasks.filter(task => task.task_list_id && loadedBoards.some(board => {
    const list = board.taskLists?.find(l => l.id === task.task_list_id);
    return list?.title?.toLowerCase() === 'todo';
  })).length;
  stats.value.inProgress = allTasks.filter(task => task.task_list_id && loadedBoards.some(board => {
    const list = board.taskLists?.find(l => l.id === task.task_list_id);
    return list?.title?.toLowerCase() === 'in progress';
  })).length;
  stats.value.done = allTasks.filter(task => task.task_list_id && loadedBoards.some(board => {
    const list = board.taskLists?.find(l => l.id === task.task_list_id);
    return list?.title?.toLowerCase() === 'done';
  })).length;
}

const displayName = computed(() => {
  if (user.value?.firstName || user.value?.lastName) {
    return `${user.value?.first_ame ?? ""} ${user.value?.lastName ?? ""}`.trim();
  }

  console.log(user.value?.firstName);
  
  return user.value?.email || "Nutzer";
});

const handleLogout = () => {
  logout()
  router.push({ name: 'login' })
}

onMounted(refreshBoards);
</script>

<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <div>
        <h1>Dashboard</h1>
        <p>Willkommen zurück, {{ displayName }}.</p>
      </div>
      <div class="setting">
        <router-link class="to_profile" to="/profile">
          Profil bearbeiten
        </router-link>
        <button @click="handleLogout">Abmelden</button>
      </div>

    </header>

    <section class="panel">
      <h2>Schnellzugriff</h2>
      <div class="cards">
        <router-link class="card" to="/boards">To Boards</router-link>
      </div>
    </section>

    <section class="panel">
      <h2>Du bist in diese Boards angemeldet:</h2>

      <ul class="board-list">
        <li v-for="board in boards" :key="board.id" class="board-item">
          {{ board.title }}
        </li>
      </ul>



    </section>

    <section class="panel stats-panel">
      <h2>Deine Aufgaben-Übersicht</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-label">Gesamt</span>
          <strong>{{ stats.total }}</strong>
        </div>
        <div class="stat-card">
          <span class="stat-label">Todo</span>
          <strong>{{ stats.todo }}</strong>
        </div>
        <div class="stat-card">
          <span class="stat-label">In Progress</span>
          <strong>{{ stats.inProgress }}</strong>
        </div>
        <div class="stat-card">
          <span class="stat-label">Done</span>
          <strong>{{ stats.done }}</strong>
        </div>
      </div>
    </section>

  </div>
</template>



<style scoped>
.dashboard {
  max-width: 900px;
  margin: 3rem auto;
  padding: 0 1rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  margin: 0;
  font-size: 2rem;
}

.dashboard-header button {
  padding: 0.75rem 1.2rem;
  border: none;
  border-radius: 8px;
  background: #dc3545;
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.dashboard-header button:hover {
  background: #c82333;
}

.to_profile {
  padding: 10px 10px;
  border: none;
  border-radius: 8px;
  background: #dc3545;
  color: white;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
}

.panel {
  margin-bottom: 1.75rem;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
  border-radius: 14px;
  background: #fff;
}

.setting {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
}

.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-card {
  padding: 1rem;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  background: #f8f9fa;
  text-align: center;
}

.stat-label {
  display: block;
  color: #6c757d;
  margin-bottom: 0.5rem;
}

.card {
  display: block;
  padding: 1.3rem;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  text-decoration: none;
  color: #212529;
  background: #f8f9fa;
  text-align: center;
  font-weight: 600;
}

.card:hover {
  background: #e9ecef;
}
</style>
