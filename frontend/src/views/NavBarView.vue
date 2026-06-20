<script setup>
import { useRouter } from 'vue-router'
import { logout } from '../services/auth'

const router = useRouter()

const handleLogout = () => {
  logout()
  router.push({ name: 'login' })
}
</script>


<template>
    <nav class="navbar">
        <div class="logo">
            TaskManager
        </div>

        <div class="links">
            <router-link to="/boards">Boards</router-link>
            <router-link to="/tasks">Tasks</router-link>
        </div>

        <div class="auth">
            <button @click="handleLogout">
                Abmelden
            </button>
        </div>
    </nav>
</template>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #2c3e50;
    color: white;
}

.links a {
    margin: 0 10px;
    color: white;
    text-decoration: none;
}

button {
    margin-left: 10px;
    padding: 6px 10px;
    cursor: pointer;
}
</style>