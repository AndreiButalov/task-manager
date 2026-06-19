<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";
import { loadProfile } from "../services/boardService.js";

const firstName = ref("");
const lastName = ref("");
const email = ref("");
const success = ref("");

async function load() {
    const profile = await loadProfile();

    firstName.value = profile.firstName;
    lastName.value = profile.lastName;
    email.value = profile.email;
}

async function saveProfile() {
    await api.put("/me", {
        firstName: firstName.value,
        lastName: lastName.value,
    });

    success.value = "Profil gespeichert!";
}

onMounted(load);
</script>

<template>
    <div>
        <h1>Profil bearbeiten</h1>

        <div>
            <label>Vorname</label>
            <input v-model="firstName" />
        </div>

        <div>
            <label>Nachname</label>
            <input v-model="lastName" />
        </div>

        <div>
            <label>E-Mail</label>
            <input :value="email" disabled />
        </div>

        <button @click="saveProfile">
            Speichern
        </button>

        <router-link class="to_profile" to="/dashboard">
            Dashboard
        </router-link>

        <p v-if="success">{{ success }}</p>
    </div>
</template>