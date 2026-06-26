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
              <button @click="editBoard(board.id)">
                Edit
              </button>

              <button @click="confirmDeleteBoard(board.id)" class="danger">
                Delete
              </button>
            </div>
          </div>

          <div class="members-section">
            <p><strong>Mitglieder:</strong></p>
            <div class="all_members">
              <div class="member" v-for="member in board.members" :key="member.id">
                {{ formatMemberName(member) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div v-if="showDeleteBoardModal" class="modal-overlay">
    <div class="modal">
      <h3>Board wirklich löschen?</h3>
      <div class="modal-actions">
        <button class="danger" @click="deleteBoard">
          Ja, löschen
        </button>
        <button @click="showDeleteBoardModal = false">
          Abbrechen
        </button>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router"
import api from "../services/api";
import { loadBoards, loadBoardMembers, loadAvailableMembers, formatMemberName } from '../services/boardService';

const boards = ref([])
const newTitle = ref("")
const error = ref("")
const memberSelection = ref({})
const availableMembers = ref({})
const router = useRouter()
const showDeleteBoardModal = ref(false)
const boardToDelete = ref(null)
const showErrorModal = ref(false)
const errorMessage = ref("")


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


async function deleteBoard() {
  try {
    await api.delete(`/boards/${boardToDelete.value}`);
    boardToDelete.value = null;
    showDeleteBoardModal.value = false;
    await refreshBoards();
  } catch (err) {
    console.error(err);
    showDeleteBoardModal.value = false;
    errorMessage.value = "Board konnte nicht gelöscht werden.";
    showErrorModal.value = true;
  }
}


function confirmDeleteBoard(id) {
  boardToDelete.value = id;
  showDeleteBoardModal.value = true;
}


async function loadMembers(board) {
  board.members = await loadBoardMembers(board.id);
  availableMembers.value[board.id] = await loadAvailableMembers(board.id);
  memberSelection.value[board.id] = availableMembers.value[board.id]?.[0]?.id || null;
}


function editBoard(boardId) {
  router.push({ name: "boardEdit", params: { id: boardId } })
}


onMounted(refreshBoards);
</script>


<style scoped>
.boards_content {
  padding: 30px;
}

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

.boards_content button,
.modal-actions button {
  color: black;
  border: #E0E0E0 solid 1px;
  background: transparent;
  padding: 10px 25px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 20px;
  cursor: pointer;
}

.boards_content button:hover,
.modal-actions button:hover {
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

.board_title_edit {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  font-size: 24px;
}

.board-actions {
  display: flex;
  gap: 20px;
}

.all_members {
  height: 40px;
  overflow-y: auto;
  cursor: pointer;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal {
  background: white;
  padding: 20px;
  border-radius: 10px;
  min-width: 300px;
  text-align: center;
}

.modal-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  justify-content: center;
}

.danger {
  background: red;
  color: white;
}
</style>