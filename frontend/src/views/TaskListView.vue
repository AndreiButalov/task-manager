<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

const taskLists = ref([]);

async function loadTaskList() {
  const response = await api.get("/tasklists");
  taskLists.value = response.data;
}

onMounted(loadTaskList);
</script>

<template>
  <div>
    <h1>Task Lists</h1>

    <ul>
      <li v-for="taskList in taskLists" :key="taskList.id">
        <strong>{{ taskList.title }}</strong>
        <br />
        ID: {{ taskList.id }}
        <br />
        Position: {{ taskList.position }}
        <br />
        Board: {{ taskList.board_id }}
      </li>
    </ul>
  </div>
</template>