<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { loadBoard, createTask, updateTask, deleteTask } from '../services/boardService';

const route = useRoute()
const board = ref(null)
const error = ref('')
const loading = ref(true)
const boardId = Number(route.params.id)
const columnTitles = ['Todo', 'In Progress', 'Done']
const newTaskTitles = ref({ 0: '', 1: '', 2: '' })
const newTaskDescriptions = ref({ 0: '', 1: '', 2: '' })
const showTaskForm = ref({ 0: false, 1: false, 2: false })
const editing = ref({})
const editTitles = ref({})
const editDescriptions = ref({})
const currentElement = ref('')
const formErrors = ref({0: '', 1: '', 2: ''})
const columns = computed(() => columnTitles.map((title, index) => ({ title, position: index })))


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
  let list = getList(position);
  const title = newTaskTitles.value[position]?.trim();
  const description = newTaskDescriptions.value[position]?.trim();

  formErrors.value[position] = '';

  if (!title) {
    formErrors.value[position] = 'Task-Titel darf nicht leer sein.';
    return;
  }

  await createTask(list.id, title, description, getTasks(list).length);

  newTaskTitles.value[position] = '';
  newTaskDescriptions.value[position] = '';
  formErrors.value[position] = '';
  showTaskForm.value[position] = false;

  await loadBoardDetail();
}


function startEdit(task) {
  editing.value[task.id] = true;
  editTitles.value[task.id] = task.title;
  editDescriptions.value[task.id] = task.description || '';
}


async function saveEdit(task) {
  const title = editTitles.value[task.id]?.trim();
  const description = editDescriptions.value[task.id] ?? null;
  if (!title) {
    error.value = 'Task-Titel darf nicht leer sein.';
    return;
  }

  try {
    await updateTask(task.id, { title, description });
    editing.value[task.id] = false;
    await loadBoardDetail();
  } catch (err) {
    console.error(err);
    error.value = 'Task konnte nicht gespeichert werden.';
  }
}


function cancelEdit(task) {
  editing.value[task.id] = false;
}


async function deleteTaskById(id) {
  if (!confirm('Task wirklich löschen?')) return;
  try {
    await deleteTask(id);
    await loadBoardDetail();
  } catch (err) {
    console.error(err);
    error.value = 'Task konnte nicht gelöscht werden.';
  }
}


function formatDate(dateString) {
  if (!dateString) return '';
  const d = new Date(dateString);
  if (isNaN(d.getTime())) return dateString;
  return d.toLocaleString(undefined, {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
}


function startDragging(taskId) {
  currentElement.value = taskId;
}


async function onDrop(targetPosition) {
  const taskId = currentElement.value;
  if (!taskId) return;

  const targetList = getList(targetPosition);
  if (!targetList) return;

  const newPosition = getTasks(targetList).length;

  try {
    await updateTask(taskId, {
      task_list_id: targetList.id,
      position: newPosition
    });

    currentElement.value = null;
    await loadBoardDetail();

  } catch (err) {
    console.error(err);
    error.value = "Task konnte nicht verschoben werden.";
  }
}


onMounted(loadBoardDetail);

</script>


<template>
  <div class="board-single">

    <div v-if="loading">Lädt Board...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else>
      <header class="board-header">
        <h1>{{ board.title }}</h1>
        <p>Erstellt von: {{ board.owner.email }}</p>
      </header>

      <section class="board-tasklists">
        <div class="kanban-grid">
          <div v-for="column in columns" :key="column.position" class="kanban-column" @dragover.prevent
            @drop="onDrop(column.position)">
            <header class="column-header">
              <h2>{{ column.title }}</h2>
              <button class="add-list-btn"
                @click="showTaskForm[column.position] = !showTaskForm[column.position]">+</button>
            </header>

            <div v-if="showTaskForm[column.position]" class="task-form-quick">
              <input type="text" v-model="newTaskTitles[column.position]" placeholder="Neue Aufgabe" />

              <textarea v-model="newTaskDescriptions[column.position]" placeholder="Beschreibung (optional)" />

              <div v-if="formErrors[column.position]" class="form-error">
                {{ formErrors[column.position] }}
              </div>

              <button @click="addTask(column.position)">
                Task hinzufügen
              </button>

              <button @click="showTaskForm[column.position] = false" class="cancel-btn">
                Abbrechen
              </button>
            </div>

            <div v-if="getList(column.position)">
              <div class="tasklist-card">

                <h3>{{ getList(column.position).title }}</h3>

                <div class="tasks">

                  <div v-for="task in getTasks(getList(column.position))" :key="task.id" class="task-card"
                    draggable="true" @dragstart="startDragging(task.id)">

                    <div v-if="editing[task.id]" class="edit_task">

                      <div class="edit_input">
                        <input class="input" type="text" v-model="editTitles[task.id]" />
                        <textarea class="textarea" v-model="editDescriptions[task.id]"></textarea>
                      </div>

                      <div class="task-edit">
                        <button @click="saveEdit(task)">Speichern</button>
                        <button @click="cancelEdit(task)" class="cancel-btn">
                          Abbrechen
                        </button>
                      </div>

                    </div>

                    <div v-else>

                      <strong>{{ task.title }}</strong>

                      <div class="task-meta">
                        <small>
                          Erstellt: {{ formatDate(task.createdAt) }}
                          <span v-if="task.dueDate">
                            • Fällig: {{ formatDate(task.dueDate) }}
                          </span>
                        </small>
                      </div>

                      <p v-if="task.description">
                        {{ task.description }}
                      </p>

                      <div class="task-actions">
                        <button class="nav_button" @click="startEdit(task)">
                          Bearbeiten
                        </button>
                        <button class="nav_button" @click="deleteTaskById(task.id)">
                          Löschen
                        </button>
                      </div>
                    </div>
                  </div>
                  <div v-if="getTasks(getList(column.position)).length === 0" class="task-card empty">
                    Keine Tasks
                  </div>

                </div>

              </div>
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
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.column-header h2 {
  margin: 0;
}

.add-list-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #0d6efd;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-list-btn:hover {
  background: #0b5ed7;
}

.task-form-quick {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: white;
  border: 1px solid #ced4da;
  border-radius: 10px;
}

.task-form-quick input,
.task-form-quick textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 6px;
  box-sizing: border-box;
}

.task-form-quick textarea {
  min-height: 80px;
  resize: vertical;
}

.task-form-quick button {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  background: #198754;
  color: white;
  cursor: pointer;
}

.task-form-quick button.cancel-btn {
  background: #6c757d;
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

.textarea {
  height: 100px;
  font-size: 16px;
  resize: none;
  overflow-y: auto;
  box-sizing: border-box;
  width: 100%;
}

.input,
.textarea {
  padding: 7px;
  width: 100%;
  border-radius: 12px;
  border: 1.5px solid lightgrey;
  outline: none;
  transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
  box-shadow: 0px 0px 20px -18px;
  margin-right: 15px;
  box-sizing: border-box;
}

.input:hover,
.textarea:hover {
  border: 2px solid lightgrey;
  box-shadow: 0px 0px 20px -17px;
}

.input:active,
textarea:active {
  transform: scale(0.95);
}

.input:focus,
textarea:focus {
  border: 2px solid grey;
}

.task-meta {
  color: #6c757d;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.task-card p,
strong {
  word-break: break-word;
  overflow-wrap: break-word;
  max-height: 100px;
  overflow-y: auto;
  cursor: pointer;
}

.task-actions {
  margin-top: 0.5rem;
  display: flex;
  justify-content: space-between;
}

.task-actions button,
.task-edit button {
  color: black;
  border: #E0E0E0 solid 1px;
  background: transparent;
  padding: 5px 10px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 20px;
  cursor: pointer;
}

.task-actions button:hover,
.task-edit button:hover {
  color: #c82333;
  border: #c82333 solid 1px;
  background: #eca6ad;
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

.error {
  color: #c82333;
}

.edit_task {
  display: flex;
  flex-direction: column;
}

.edit_input {
  margin-bottom: 15px;
  display: flex;
  gap: 10px;
  flex-direction: column;
}

.task-edit {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-error {
  color: #dc3545;
  font-size: 0.85rem;
  margin: 6px 0;
}
</style>
