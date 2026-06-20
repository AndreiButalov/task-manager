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
      <button v-if="!isLoggedIn" @click="goLogin">Login</button>
      <button v-if="!isLoggedIn" @click="goRegister">Register</button>

      <button v-if="isLoggedIn" @click="logout">Logout</button>
    </div>
  </nav>
</template>

<script>
// import api from "@/services/api";

export default {
  data() {
    return {
      isLoggedIn: false
    };
  },

  mounted() {
    this.checkAuth();
  },

  methods: {
    checkAuth() {
      this.isLoggedIn = !!localStorage.getItem("token");
    },

    goLogin() {
      this.$router.push("/login");
    },

    goRegister() {
      this.$router.push("/register");
    },

    logout() {
      localStorage.removeItem("token");
      this.isLoggedIn = false;
      this.$router.push("/login");
    }
  }
};
</script>

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