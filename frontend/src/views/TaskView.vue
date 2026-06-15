<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "@/services/api";

const route = useRoute();
const task = ref(null);

const loadTask = async () => {
  try {
    const res = await api.get("/tasks");

    task.value = res.data.find(
      (t) => t.id === Number(route.params.id)
    );
  } catch (error) {
    console.error("Error loading task:", error);
  }
};

onMounted(loadTask);
</script>

<template>
  <div class="task-page" v-if="task">
    <h1>{{ task.title }}</h1>

    <p v-if="task.description">
      {{ task.description }}
    </p>

    <div class="meta">
      <p><strong>ID:</strong> {{ task.id }}</p>
      <p><strong>Position:</strong> {{ task.position }}</p>
      <p><strong>TaskList ID:</strong> {{ task.task_list_id }}</p>
    </div>
  </div>

  <div v-else>
    <p>Loading task...</p>
  </div>
</template>

<style scoped>
.task-page {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.meta {
  margin-top: 20px;
  font-size: 14px;
  color: #666;
}
</style>