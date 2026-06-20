<script setup>

import { ref, onMounted } from "vue";
import { useRouter } from "vue-router"
import api from "../services/api";
import { loadBoards, loadBoardMembers, loadAvailableMembers } from '../services/boardService';

const boards = ref([]);
const newTitle = ref("");
const error = ref("");
const memberSelection = ref({});
const availableMembers = ref({});
const router = useRouter()


async function refreshBoards() {
  boards.value = await loadBoards();
  await Promise.all(boards.value.map((board) => loadMembers(board)));
}

function formatMemberName(member) {
  const user = member.user ?? member

  const fullName = `${user.firstName ?? ""} ${user.lastName ?? ""}`.trim()

  return fullName || user.email || "Unbekannt"
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
  console.log(board.members)

  availableMembers.value[board.id] = await loadAvailableMembers(board.id);
  memberSelection.value[board.id] = availableMembers.value[board.id]?.[0]?.id || null;
}

function openBoard(boardId) {
  router.push({ name: "boardSingle", params: { id: boardId } })
}

function editBoard(boardId) {
  router.push({ name: "boardEdit", params: { id: boardId } })
}


onMounted(refreshBoards);
</script>

<template>
  <div class="boards_content">
    <h1>Boards</h1>
    <div class="board_create">
      <form @submit.prevent="createBoard">
        <input v-model="newTitle" placeholder="Neues Board..." />
        <button>Create</button>
      </form>
    </div>

    <hr />
    <div class="boards_body">
      <div class="board-card" v-for="board in boards" :key="board.id">
        <div class="board_content">
          <div class="board_title_edit">
            <router-link class="board-title" :to="{ name: 'boardSingle', params: { id: board.id } }">
              {{ board.title }}
            </router-link>
            <div class="board-actions">
              <button @click="openBoard(board.id)">
                Open
              </button>

              <button @click="editBoard(board.id)">
                Edit
              </button>

              <button @click="deleteBoard(board.id)" class="danger">
                Delete
              </button>
            </div>
          </div>

          <div class="members-section">
            <p><strong>Mitglieder:</strong></p>
            <div class="member" v-for="member in board.members" :key="member.id">
            {{ formatMemberName(member) }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <div><router-link class="card" to="/dashboard">Dashboard</router-link></div>
  </div>
</template>

<style scoped>
.boards_content input,
select {
  padding: 7px;
  border-radius: 12px;
  border: 1.5px solid lightgrey;
  outline: none;
  transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
  box-shadow: 0px 0px 20px -18px;
  margin-right: 15px;
}

.boards_content input:hover,
select:hover {
  border: 2px solid lightgrey;
  box-shadow: 0px 0px 20px -17px;
}

.boards_content input:active,
select:active {
  transform: scale(0.95);
}

.boards_content input:focus,
select:focus {
  border: 2px solid grey;
}

.boards_content button {
  color: black;
  border: #E0E0E0 solid 1px;
  background: transparent;
  padding: 10px 25px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 20px;
  cursor: pointer;
}

.boards_content button:hover {
  color: #c82333;
  border: #c82333 solid 1px;
  background: #eca6ad;

}

.board_content {
  margin-bottom: 1.75rem;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
  border-radius: 14px;
  background: #fff;
}

.board-title {
  text-decoration: none;
  color: black;
  cursor: pointer;
}
</style>