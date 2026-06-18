<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { loadBoard, createTask, createTaskList } from '../services/boardService';

const route = useRoute();
const router = useRouter();
const board = ref(null);
const error = ref('');
const loading = ref(true);
const boardId = Number(route.params.id);
const columnTitles = ['Todo', 'In Progress', 'Done'];
const newTaskTitles = ref({ 0: '', 1: '', 2: '' });
const newTaskDescriptions = ref({ 0: '', 1: '', 2: '' });
const newListNames = ref({ 0: '', 1: '', 2: '' });
const showTaskForm = ref({ 0: false, 1: false, 2: false });

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

function getList(position) {
  return board.value?.taskLists?.find((list) => list.position === position) || null;
}

function getTasks(list) {
  return list?.tasks?.slice().sort((a, b) => a.position - b.position) || [];
}

async function addTask(position) {
  const list = getList(position);
  const title = newTaskTitles.value[position]?.trim();
  const description = newTaskDescriptions.value[position]?.trim();

  if (!list) {
    error.value = 'Es gibt keine Taskliste für diese Spalte.';
    return;
  }

  if (!title) {
    error.value = 'Task-Titel darf nicht leer sein.';
    return;
  }

  await createTask(list.id, title, description, getTasks(list).length);
  newTaskTitles.value[position] = '';
  newTaskDescriptions.value[position] = '';
  showTaskForm.value[position] = false;
  await loadBoardDetail();
}

async function addTaskList(position) {
  const title = newListNames.value[position]?.trim() || columnTitles[position];

  await createTaskList(boardId, title, position);
  newListNames.value[position] = '';
  await loadBoardDetail();
}

function goBack() {
  router.push({ name: 'boards' });
}

const columns = computed(() => columnTitles.map((title, index) => ({ title, position: index })));

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

      <section class="board-tasklists">
        <div class="kanban-grid">
          <div v-for="column in columns" :key="column.position" class="kanban-column">
            <header class="column-header">
              <h2>{{ column.title }}</h2>
            </header>

            <div v-if="getList(column.position)">
              <div class="tasklist-card">
                <h3>{{ getList(column.position).title }}</h3>
                <ul class="tasks">
                  <li v-for="task in getTasks(getList(column.position))" :key="task.id" class="task-card">
                    <strong>{{ task.title }}</strong>
                    <p v-if="task.description">{{ task.description }}</p>
                  </li>
                  <li v-if="getTasks(getList(column.position)).length === 0" class="task-card empty">
                    Keine Tasks
                  </li>
                </ul>

                <div class="task-form">
                  <button class="toggle-task-form" @click="showTaskForm[column.position] = !showTaskForm[column.position]">
                    {{ showTaskForm[column.position] ? 'Formular ausblenden' : 'Neue Aufgabe' }}
                  </button>

                  <div v-if="showTaskForm[column.position]" class="task-form-fields">
                    <input
                      type="text"
                      v-model="newTaskTitles[column.position]"
                      placeholder="Neue Aufgabe"
                    />
                    <textarea
                      v-model="newTaskDescriptions[column.position]"
                      placeholder="Beschreibung (optional)"
                    />
                    <button @click="addTask(column.position)">Task hinzufügen</button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="tasklist-empty">
              <p>Keine Liste für {{ column.title }} vorhanden.</p>
              <input
                type="text"
                v-model="newListNames[column.position]"
                placeholder="Liste erstellen oder Titel eingeben"
              />
              <button @click="addTaskList(column.position)">Liste erstellen</button>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.board-single {
  max-width: 1200px;
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
.board-header {
  margin-bottom: 1.5rem;
}
.board-header h1 {
  margin: 0;
}
.kanban-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}
.kanban-column {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 14px;
  padding: 1rem;
  min-height: 400px;
}
.column-header {
  margin-bottom: 1rem;
}
.tasklist-card {
  background: white;
  border: 1px solid #ced4da;
  border-radius: 12px;
  padding: 1rem;
}
.tasks {
  list-style: none;
  padding: 0;
  margin: 0 0 1rem 0;
}
.task-card {
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 10px;
  margin-bottom: 0.75rem;
  background: #ffffff;
}
.task-card.empty {
  color: #6c757d;
  text-align: center;
  border-style: dashed;
}
.task-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.task-form input,
.task-form textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 10px;
}
.task-form textarea {
  min-height: 80px;
  resize: vertical;
}
.task-form button {
  align-self: flex-start;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 10px;
  background: #0d6efd;
  color: white;
  cursor: pointer;
}
.tasklist-empty {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.tasklist-empty input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 10px;
}
.tasklist-empty button {
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 10px;
  background: #198754;
  color: white;
  cursor: pointer;
}
.error {
  color: #c82333;
}
</style>
