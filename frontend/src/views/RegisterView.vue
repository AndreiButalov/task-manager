<template>
  <div class="auth-page">
    <h1>Registrieren</h1>
    <form @submit.prevent="handleRegister">
      <label>
        Email
        <input v-model="email" type="email" required />
      </label>

      <label>
        Passwort
        <input v-model="password" type="password" required minlength="6" />
      </label>

      <label>
        Passwort wiederholen
        <input v-model="repeatPassword" type="password" required minlength="6" />
      </label>

      <button type="submit">Registrieren</button>
      <p class="success" v-if="success">{{ success }}</p>
      <p class="error" v-if="error">{{ error }}</p>
    </form>
    <p>
      Schon einen Account? <router-link to="/login">Anmelden</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '../services/auth'

const router = useRouter()
const email = ref('')
const password = ref('')
const repeatPassword = ref('')
const error = ref('')
const success = ref('')

const handleRegister = async () => {
  error.value = ''
  success.value = ''

  if (password.value !== repeatPassword.value) {
    error.value = 'Passwörter stimmen nicht überein.'
    return
  }

  try {
    await register(email.value, password.value, repeatPassword.value)
    success.value = 'Registrierung erfolgreich. Bitte melde dich an.'
    setTimeout(() => router.push({ name: 'login' }), 1200)
  } catch (err) {
    error.value = err.response?.data?.error || 'Registrierung fehlgeschlagen'
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
  width: 100%;
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
  background: #28a745;
  color: white;
  font-weight: 700;
  border-radius: 8px;
  cursor: pointer;
}

button:hover {
  background: #218838;
}

.error {
  color: #d9534f;
  margin-top: 1rem;
}

.success {
  color: #28a745;
  margin-top: 1rem;
}
</style>
