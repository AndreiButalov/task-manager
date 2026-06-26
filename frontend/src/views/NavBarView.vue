<template>
    <nav class="navbar">
        <div class="logo">
            <img src="../assets/img/Logo.png" alt="">
        </div>

        <template v-if="!isAuthPage()">
            <div class="links">
                <router-link to="/dashboard">Dashboard</router-link>
                <router-link to="/boards">Boards</router-link>
            </div>

            <div class="setting">
                <button class="to_profile" @click="showProfileModal = true">
                    Profil bearbeiten
                </button>

                <button @click="handleLogout">
                    Abmelden
                </button>
            </div>
        </template>

        <div v-if="showProfileModal" class="window-to_profile" @click.self="showProfileModal = false">
            <div class="edit-profile-content">
                <button class="close" @click="showProfileModal = false">✕</button>
                <ProfileView @saved="showProfileModal = false" />
            </div>
        </div>
    </nav>
</template>


<script setup>
import { useRouter, useRoute } from 'vue-router'
import { logout } from '../services/auth'
import { ref } from "vue"
import ProfileView from '@/views/ProfileView.vue'

const router = useRouter()
const route = useRoute()
const showProfileModal = ref(false)


const handleLogout = () => {
    logout()
    router.push({ name: 'login' })
}


const isAuthPage = () =>
    ['login', 'register'].includes(route.name)

</script>


<style scoped>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px;
    background: #414952;
    color: white;
    position: relative;
}

h1 {
    margin-block-start: 0;
    margin-block-end: 0;
}

.card {
    text-decoration: none;
    color: white;
    cursor: pointer;
}

.links a {
    margin: 0 10px;
    color: white;
    text-decoration: none;
    font-size: 22px;
    font-weight: 700;
}

.links a:hover {
    color: #c82333;
}

.setting button {
    padding: 0.75rem 1.2rem;
    border: none;
    border-radius: 8px;
    background: #dc3545;
    color: white;
    font-weight: 700;
    cursor: pointer;
    margin-right: 20px;
}

.setting button:hover {
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

.setting {
    display: flex;
    justify-content: center;
    align-items: center;
}

button {
    margin-left: 10px;
    padding: 6px 10px;
    cursor: pointer;
}

.window-to_profile {
    position: absolute;
    right: 9px;
    top: 65px;
    width: 350px;
    height: auto;
    min-height: 300px;
    z-index: 9999;
    border-radius: 20px;
    border: #E0E0E0 solid 1px;
    background-color: #F8F9FA;
    padding: 20px;
}

.close {
    position: absolute;
    right: 0px;
    top: 0px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #746b6b;
    border: #E0E0E0 solid 1px;
    background: transparent;
    font-size: 18px;
    cursor: pointer;
    border-radius: 50%;
}

.close:hover {
    color: #c82333;
    border: #c82333 solid 1px;
    background: #eca6ad;
}

.edit-profile-content {
    position: relative;
}

.logo {
    margin-left: 20px;
}

.logo img {
    width: 70px;
}
</style>