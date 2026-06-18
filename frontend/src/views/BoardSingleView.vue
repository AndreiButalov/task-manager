<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { loadBoard } from '../services/boardService';

const route = useRoute();
const router = useRouter();
const board = ref(null);
const error = ref('');
const loading = ref(true);

const boardId = Number(route.params.id);

async function loadBoardDetail() {
  try {
    loading.value = true;
    board.value = await loadBoard(boardId);
  } catch (err) {
    console.error(err);
    error.value = err?.response?.data?.error || 'Board konnte nicht geladen werden.';
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'boards' });
}

onMounted(loadBoardDetail);
</script>

<template>
  <div class="board-single">
    <button class="back-button" @click="goBack">← Zurück zu Boards</button>

    <div v-if="loading">Lädt Board...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else>
      <header class="board-header">
        <h1>{{ board.title }}</h1>
        <p>Board-ID: {{ board.id }}</p>
        <p>Owner: {{ board.owner.email }}</p>
      </header>

      <section class="board-members">
        <h2>Mitglieder</h2>
        <ul>
          <li v-for="memberId in board.memberIds" :key="memberId">ID: {{ memberId }}</li>
        </ul>
      </section>

      <section class="board-tasklists">
        <h2>Tasklisten</h2>
        <div class="tasklists-grid">
          <div v-for="list in board.taskLists" :key="list.id" class="tasklist-card">
            <h3>{{ list.title }}</h3>
            <ul class="tasks">
              <li v-for="task in list.tasks" :key="task.id" class="task-card">
                <strong>{{ task.title }}</strong>
                <p v-if="task.description">{{ task.description }}</p>
              </li>
              <li v-if="list.tasks.length === 0" class="task-card empty">Keine Tasks</li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.board-single {
  max-width: 960px;
  margin: 2rem auto;
  padding: 1rem;
}
.back-button {
  margin-bottom: 1rem;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 8px;
  background: #6c757d;
  color: white;
  cursor: pointer;
}
.board-header h1 {
  margin: 0;
}
.board-tasklists {
  margin-top: 1.5rem;
}
.tasklists-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}
.tasklist-card {
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 1rem;
  background: #f8f9fa;
}
.task-card {
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 10px;
  margin-bottom: 0.75rem;
  background: white;
}
.task-card.empty {
  color: #6c757d;
  text-align: center;
}
.error {
  color: #c82333;
}
</style>
