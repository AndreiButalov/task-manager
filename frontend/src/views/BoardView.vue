<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";
import { loadBoards, loadBoardMembers, loadAvailableMembers, addBoardMember, removeBoardMember } from '../services/boardService';

const boards = ref([]);
const newTitle = ref("");
const editingId = ref(null);
const editTitle = ref("");
const error = ref("");
const memberSelection = ref({});
const availableMembers = ref({});

async function refreshBoards() {
  boards.value = await loadBoards();
  await Promise.all(boards.value.map((board) => loadMembers(board)));
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
  board.members = await loadBoardMembers(board.id);
  availableMembers.value[board.id] = await loadAvailableMembers(board.id);
  memberSelection.value[board.id] = availableMembers.value[board.id]?.[0]?.id || null;
}

async function addMember(board) {
  const userId = memberSelection.value[board.id];
  if (!userId) return;

  await addBoardMember(board.id, userId);
  await loadMembers(board);
}

async function removeMember(board, userId) {
  if (!confirm("Mitglied wirklich entfernen?")) return;
  await removeBoardMember(board.id, userId);
  await loadMembers(board);
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

          <div class="members-section">
            <p><strong>Mitglieder:</strong></p>
            <ul>
              <li v-for="member in board.members" :key="member.id">
                {{ member.email }}
                <button
                  v-if="member.id !== board.owner?.id"
                  @click="removeMember(board, member.id)"
                  class="small-button delete-btn"
                >Entfernen</button>
              </li>
            </ul>

            <div v-if="availableMembers[board.id]?.length" class="member-add">
              <select v-model="memberSelection[board.id]">
                <option
                  v-for="member in availableMembers[board.id]"
                  :key="member.id"
                  :value="member.id"
                >
                  {{ member.email }}
                </option>
              </select>
              <button @click="addMember(board)" class="small-button">Mitglied hinzufügen</button>
            </div>
            <p v-else class="no-more-members">Keine weiteren Nutzer verfügbar.</p>
          </div>
        </div>

      </li>
    </ul>
    <div><router-link class="card" to="/dashboard">Dashboard</router-link></div>
  </div>
</template>