<script setup>

import { ref, onMounted } from "vue";
import api from "../services/api";
import { loadProfile } from "../services/boardService.js";

const firstName = ref("");
const lastName = ref("");
const email = ref("");
const success = ref("");
const emit = defineEmits(['saved'])

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

    emit('saved')
}

onMounted(load);
</script>

<template>
    <div class="profile_content">
        <h1>Profil bearbeiten</h1>

        <div class="profile_content_form">
            <div>
                <label>Vorname:</label>
                <input class="input" v-model="firstName" />
            </div>

            <div>
                <label>Nachname:</label>
                <input class="input" v-model="lastName" />
            </div>

            <div>
                <label>E-Mail:</label>
                <input class="input" :value="email" disabled />
            </div>
        </div>

        <div class="profile_content_footer">
            <button @click="saveProfile">
                Speichern
            </button>
        </div>

        <p v-if="success">{{ success }}</p>
    </div>
</template>


<style scoped>
h1 {
    font-size: 22px;
    text-align: center;
    padding: 10px 0;
    color: black;
}

.profile_content {
    display: flex;
    flex-direction: column;
}

.profile_content_form div {
    display: flex;
    justify-content: space-between;
    align-items: center;    
    padding: 6px 0;
}

.profile_content_form label {
    font-weight: 700;
    color: black;
}

.input{  
  padding: 7px;
  border-radius: 12px;
  border: 1.5px solid lightgrey;
  outline: none;
  transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
  box-shadow: 0px 0px 20px -18px;
  margin-right: 15px;
}

.input:hover {
  border: 2px solid lightgrey;
  box-shadow: 0px 0px 20px -17px;
}

.input:active {
  transform: scale(0.95);
}

.input:focus {
  border: 2px solid grey;
}

.profile_content_footer {
    display: flex;
    justify-content: center;
    padding-top: 40px;
}

.profile_content_footer button {
    color: black;
    border: #E0E0E0 solid 1px;
    background: transparent;
    padding: 10px 25px;
    font-size: 14px;
    font-weight: 700;
    border-radius: 20px;
    cursor: pointer;
}

.profile_content_footer button:hover {
    color: #c82333;
    border: #c82333 solid 1px;
    background: #eca6ad;
    
}
</style>