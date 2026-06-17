<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <div>
        <h1>Dashboard</h1>
        <p>Willkommen zurück, {{ user?.email || 'Nutzer' }}.</p>
      </div>
      <button @click="handleLogout">Abmelden</button>
    </header>

    <section class="panel">
      <h2>Schnellzugriff</h2>
      <div class="cards">
        <router-link class="card" to="/boards">Boards</router-link>
        <router-link class="card" to="/tasklists">Tasklisten</router-link>
      </div>
    </section>

    <section class="panel">
      <h2>Was du als nächstes tun kannst</h2>
      <ul>
        <li>Erstelle neue Boards und verwalte deine Aufgaben.</li>
        <li>Lege Tasklisten an, um Arbeit zu strukturieren.</li>
        <li>Öffne ein bestehendes Board, um direkt loszulegen.</li>
      </ul>
    </section>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { getUser, logout } from '../services/auth'

const router = useRouter()
const user = getUser()

const handleLogout = () => {
  logout()
  router.push({ name: 'login' })
}
</script>

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
