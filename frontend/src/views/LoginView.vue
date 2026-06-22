<template>
  <div class="auth-page">
    <h1>Anmelden</h1>
    <form @submit.prevent="handleLogin">
      <label>
        Email
        <input v-model="email" type="email" required />
      </label>

      <label>
        Passwort
        <input v-model="password" type="password" required />
      </label>

      <button type="submit">Einloggen</button>
      <p class="error" v-if="error">{{ error }}</p>
    </form>
    <p>
      Noch keinen Account? <router-link to="/register">Registrieren</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '../services/auth'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

const handleLogin = async () => {
  error.value = ''

  try {
    await login(email.value, password.value)
    router.push({ name: 'dashboard' })
  } catch (err) {
    error.value = err.response?.data?.error || 'Login fehlgeschlagen'
  }
}
</script>

<style scoped>
.auth-page {
  max-width: 420px;
  margin: 4rem auto;
  padding: 2rem;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  background: #fff;
}

h1 {
  margin-bottom: 1.5rem;
  font-size: 1.75rem;
}

label {
  display: block;
  margin-bottom: 1rem;
  font-weight: 600;
}

input {
  width: 93%;
  padding: 0.75rem;
  margin-top: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 8px;
}

button {
  width: 100%;
  margin-top: 1rem;
  padding: 0.85rem;
  border: none;
  background: #007bff;
  color: white;
  font-weight: 700;
  border-radius: 8px;
  cursor: pointer;
}

button:hover {
  background: #0056b3;
}

.error {
  color: #d9534f;
  margin-top: 1rem;
}
</style>
