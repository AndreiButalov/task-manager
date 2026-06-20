<script setup>
import { loadBoards, loadBoard } from '../services/boardService'
import { loadProfile } from '../services/boardService'
import { ref, onMounted, computed } from "vue"

const firstName = ref("")
const lastName = ref("")
const email = ref("")
const boards = ref([])

const stats = ref({ total: 0, todo: 0, inProgress: 0, done: 0 })

const displayName = computed(() => {
  if (firstName.value || lastName.value) {
    return `${firstName.value ?? ""} ${lastName.value ?? ""}`.trim()
  }
  return email.value || "Nutzer"
})


async function refreshBoards() {
  boards.value = await loadBoards()
  await loadTaskStats()
}


async function loadTaskStats() {
  const loadedBoards = await Promise.all(
    boards.value.map(board => loadBoard(board.id))
  )

  const allTasks = loadedBoards.flatMap(
    board => board.taskLists?.flatMap(list => list.tasks || []) || []
  )

  stats.value.total = allTasks.length

  stats.value.todo = allTasks.filter(task =>
    loadedBoards.some(board =>
      board.taskLists?.find(l =>
        l.id === task.task_list_id &&
        l.title?.toLowerCase() === 'todo'
      )
    )
  ).length

  stats.value.inProgress = allTasks.filter(task =>
    loadedBoards.some(board =>
      board.taskLists?.find(l =>
        l.id === task.task_list_id &&
        l.title?.toLowerCase() === 'in progress'
      )
    )
  ).length

  stats.value.done = allTasks.filter(task =>
    loadedBoards.some(board =>
      board.taskLists?.find(l =>
        l.id === task.task_list_id &&
        l.title?.toLowerCase() === 'done'
      )
    )
  ).length
}


async function load() {
  const profile = await loadProfile()

  firstName.value = profile.firstName
  lastName.value = profile.lastName
  email.value = profile.email
}

onMounted(async () => {
  await Promise.all([
    refreshBoards(),
    load()
  ])
})
</script>

<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <div>
        <h1>Dashboard</h1>
        <p>
          Willkommen zurück, {{ displayName }}.
        </p>
      </div>     
    </header>    

    <section class="panel">
      <h2>Schnellzugriff</h2>
      <div class="cards">
        <router-link class="card" to="/boards">
          To Boards
        </router-link>
      </div>
    </section>

    <section class="panel">
      <h2>Du bist in diese Boards angemeldet:</h2>
      <ul class="board-list">
        <li v-for="board in boards" :key="board.id">
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
  padding: 1rem;
}

.dashboard-header {
  position: relative;
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

.setting button {
  padding: 0.75rem 1.2rem;
  border: none;
  border-radius: 8px;
  background: #dc3545;
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.setting button:hover {
  background: #c82333;
}

.panel {
  margin-bottom: 1.75rem;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
  border-radius: 14px;
  background: #fff;
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
