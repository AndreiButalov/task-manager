<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

const boards = ref([]);

async function loadBoards() {
  const response = await api.get("/boards");
  boards.value = response.data;
}

onMounted(loadBoards);
</script>

<template>
  <div>
    <h1>Boards</h1>

    <ul>
      <li v-for="board in boards" :key="board.id">
        {{ board.title }}
      </li>
    </ul>
  </div>
</template>