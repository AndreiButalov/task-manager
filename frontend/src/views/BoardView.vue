<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";
import { loadBoards } from '../services/boardService';

const boards = ref([]);
const newTitle = ref("");
const editingId = ref(null);
const editTitle = ref("");
const error = ref("");

async function refreshBoards() {
  boards.value = await loadBoards();
}

async function createBoard() {
  const title = newTitle.value.trim();

  if (!title) {
    error.value = "Titel darf nicht leer sein";
    return;
  }

  await api.post("/boards", {
    title,
  });

  newTitle.value = "";
  await refreshBoards();
}

async function deleteBoard(id) {
  if (!confirm("Board wirklich löschen?")) return;

  await api.delete(`/boards/${id}`);
  await refreshBoards();
}

async function loadMembers(board) {
  const response = await api.get(`/boards/${board.id}/members`);
  board.members = response.data;
}

function hideMembers(board) {
  delete board.members;
}

function startEdit(board) {
  editingId.value = board.id;
  editTitle.value = board.title;
}

function cancelEdit() {
  editingId.value = null;
  editTitle.value = "";
}

async function saveEdit(id) {
  if (!editTitle.value.trim()) return;

  await api.put(`/boards/${id}`, {
    title: editTitle.value,
  });

  editingId.value = null;
  editTitle.value = "";
  await refreshBoards();
}

onMounted(refreshBoards);
</script>

<template>
  <div>
    <h1>Boards</h1>
    <form @submit.prevent="createBoard">
      <input v-model="newTitle" placeholder="Neues Board..." />
      <button>Create</button>
    </form>

    <hr />

    <ul>
      <li v-for="board in boards" :key="board.id">        
        <div v-if="editingId === board.id">
          <input v-model="editTitle" />
          <button @click="saveEdit(board.id)">Save</button>
          <button @click="cancelEdit">Cancel</button>
        </div>
        <div v-else>
          <router-link :to="{ name: 'boardSingle', params: { id: board.id } }">{{ board.title }}</router-link>

          <button @click="startEdit(board)">Edit</button>
          <button @click="deleteBoard(board.id)">Delete</button>
          <button @click="loadMembers(board)">Mitglieder laden</button>
          <button v-if="board.members" @click="hideMembers(board)">Mitglieder ausblenden</button>

          <ul v-if="board.members">
            <li v-for="member in board.members" :key="member.id">
              {{ member.email }}
            </li>
          </ul>
        </div>

      </li>
    </ul>
    <div><router-link class="card" to="/dashboard">Dashboard</router-link></div>
  </div>
</template>