<script setup>
import { ref, onMounted } from "vue";
import { loadTaskLists } from "../services/boardService";

const taskLists = ref([]);

async function loadTaskList() {
  taskLists.value = await loadTaskLists();
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