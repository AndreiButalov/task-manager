<template>
  <div class="home">
    <h1>Home</h1>
    <p>Willkommen auf der Startseite</p>
    <nav>
      <router-link to="/boards">Zu Boards</router-link>
      <!-- <router-link to="/tasks">Zu Tasks</router-link> -->
    </nav>
  </div>
</template>

<style scoped>
.home {
  padding: 2rem;
}

nav {
  margin-top: 2rem;
}

a {
  display: inline-block;
  margin-right: 1rem;
  padding: 0.5rem 1rem;
  background: #007bff;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

a:hover {
  background: #0056b3;
}
</style>
