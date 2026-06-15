<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../services/api";

const route = useRoute();
const router = useRouter();
const task = ref(null);
const isLoading = ref(true);
const isEditing = ref(false);
const error = ref(null);

const editData = ref({
  title: "",
  description: "",
  dueDate: "",
});

async function loadTask() {
  try {
    isLoading.value = true;
    error.value = null;
    const taskId = route.params.id;
    const response = await api.get(`/tasks/${taskId}`);
    task.value = response.data;
    editData.value = {
      title: response.data.title,
      description: response.data.description || "",
      dueDate: response.data.dueDate ? response.data.dueDate.split("T")[0] : "",
    };
  } catch (err) {
    error.value = "Fehler beim Laden der Task";
    console.error(err);
  } finally {
    isLoading.value = false;
  }
}

async function updateTask() {
  try {
    const response = await api.put(`/tasks/${route.params.id}`, {
      title: editData.value.title,
      description: editData.value.description,
      dueDate: editData.value.dueDate ? new Date(editData.value.dueDate) : null,
    });
    task.value = response.data;
    isEditing.value = false;
  } catch (err) {
    error.value = "Fehler beim Aktualisieren der Task";
    console.error(err);
  }
}

async function deleteTask() {
  if (!confirm("Möchtest du diese Task wirklich löschen?")) return;

  try {
    await api.delete(`/tasks/${route.params.id}`);
    router.push("/tasks");
  } catch (err) {
    error.value = "Fehler beim Löschen der Task";
    console.error(err);
  }
}

function cancelEdit() {
  isEditing.value = false;
  editData.value = {
    title: task.value.title,
    description: task.value.description || "",
    dueDate: task.value.dueDate ? task.value.dueDate.split("T")[0] : "",
  };
}

function goBack() {
  router.back();
}

onMounted(loadTask);
</script>

<template>
  <div class="task-container">
    <button @click="goBack" class="btn btn-back">← Zurück</button>

    <div v-if="isLoading" class="loading">Lädt...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else-if="task" class="task-detail">
      <div class="task-header">
        <h1 v-if="!isEditing">{{ task.title }}</h1>
        <input
          v-else
          v-model="editData.title"
          type="text"
          class="task-title-input"
          placeholder="Task-Titel"
        />

        <div class="task-meta">
          <span class="meta-item">
            <strong>Erstellt:</strong> {{ new Date(task.createdAt).toLocaleDateString("de-DE") }}
          </span>
          <span v-if="task.dueDate" class="meta-item due-date">
            <strong>Fällig:</strong> {{ new Date(task.dueDate).toLocaleDateString("de-DE") }}
          </span>
          <span class="meta-item">
            <strong>Position:</strong> {{ task.position }}
          </span>
        </div>
      </div>

      <div class="task-description-section">
        <h2>Beschreibung</h2>
        <div v-if="!isEditing" class="description-view">
          <p v-if="task.description" class="description-text">{{ task.description }}</p>
          <p v-else class="no-description">Keine Beschreibung vorhanden</p>
        </div>
        <textarea
          v-else
          v-model="editData.description"
          class="description-textarea"
          placeholder="Beschreibung der Task..."
          rows="5"
        ></textarea>
      </div>

      <div v-if="!isEditing" class="task-due-date-section">
        <h2>Fälligkeitsdatum</h2>
        <p v-if="task.dueDate" class="due-date-text">
          {{ new Date(task.dueDate).toLocaleDateString("de-DE") }}
        </p>
        <p v-else class="no-due-date">Kein Fälligkeitsdatum gesetzt</p>
      </div>

      <div v-else class="task-due-date-section">
        <h2>Fälligkeitsdatum</h2>
        <input v-model="editData.dueDate" type="date" class="due-date-input" />
      </div>

      <div class="task-actions">
        <div v-if="isEditing" class="edit-actions">
          <button @click="updateTask" class="btn btn-primary">Speichern</button>
          <button @click="cancelEdit" class="btn btn-secondary">Abbrechen</button>
        </div>
        <div v-else class="view-actions">
          <button @click="isEditing = true" class="btn btn-primary">Bearbeiten</button>
          <button @click="deleteTask" class="btn btn-danger">Löschen</button>
        </div>
      </div>

      <div v-if="task.taskList" class="task-list-info">
        <strong>Task-Liste:</strong>
        <router-link :to="{ name: 'taskList', params: { id: task.taskList.id } }">
          {{ task.taskList.title }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.task-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.btn-back {
  background-color: transparent;
  color: #007bff;
  border: none;
  cursor: pointer;
  font-size: 16px;
  padding: 8px 0;
  margin-bottom: 20px;
  transition: color 0.2s;
}

.btn-back:hover {
  color: #0056b3;
}

.task-header {
  margin-bottom: 30px;
}

.task-header h1 {
  margin: 0 0 15px 0;
  font-size: 32px;
  color: #333;
}

.task-title-input {
  width: 100%;
  padding: 12px;
  font-size: 28px;
  font-weight: bold;
  border: 2px solid #007bff;
  border-radius: 4px;
  margin-bottom: 15px;
}

.task-title-input:focus {
  outline: none;
  border-color: #0056b3;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.task-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  font-size: 14px;
  color: #666;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-item strong {
  color: #333;
}

.task-description-section,
.task-due-date-section {
  margin-bottom: 30px;
}

.task-description-section h2,
.task-due-date-section h2 {
  font-size: 18px;
  margin: 0 0 15px 0;
  color: #333;
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 10px;
}

.description-view {
  background-color: #f9f9f9;
  padding: 15px;
  border-radius: 4px;
  border-left: 4px solid #007bff;
}

.description-text {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
  line-height: 1.6;
  color: #555;
}

.no-description,
.no-due-date {
  margin: 0;
  color: #999;
  font-style: italic;
}

.description-textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #007bff;
  border-radius: 4px;
  font-family: inherit;
  font-size: 14px;
  resize: vertical;
}

.description-textarea:focus {
  outline: none;
  border-color: #0056b3;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.due-date-text {
  margin: 0;
  font-size: 16px;
  color: #333;
}

.due-date-input {
  padding: 10px;
  border: 2px solid #007bff;
  border-radius: 4px;
  font-size: 14px;
}

.due-date-input:focus {
  outline: none;
  border-color: #0056b3;
}

.task-actions {
  margin: 40px 0;
  display: flex;
  gap: 10px;
}

.edit-actions,
.view-actions {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}

.task-list-info {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #ddd;
  font-size: 14px;
  color: #666;
}

.task-list-info a {
  color: #007bff;
  text-decoration: none;
  font-weight: 600;
}

.task-list-info a:hover {
  text-decoration: underline;
}

.loading,
.error {
  text-align: center;
  padding: 40px 20px;
  font-size: 16px;
}

.error {
  color: #dc3545;
  background-color: #f8d7da;
  padding: 15px;
  border-radius: 4px;
}
</style>
