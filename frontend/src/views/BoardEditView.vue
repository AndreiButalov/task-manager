<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import api from "../services/api"
import {
  loadBoardMembers,
  loadAvailableMembers,
  addBoardMember,
  removeBoardMember
} from "../services/boardService"

const route = useRoute()
const router = useRouter()

const boardId = route.params.id

const board = ref(null)
const title = ref("")
const members = ref([])
const availableMembers = ref([])
const memberSelection = ref(null)
const error = ref("")

async function loadBoard() {
  const res = await api.get(`/boards/${boardId}`)
  board.value = res.data
  title.value = board.value.title
}

async function loadMembers() {
  members.value = await loadBoardMembers(boardId)
  availableMembers.value = await loadAvailableMembers(boardId)
  memberSelection.value = availableMembers.value?.[0]?.id || null
}

async function saveTitle() {
  if (!title.value.trim()) return

  await api.put(`/boards/${boardId}`, {
    title: title.value
  })

  error.value = "Gespeichert!"
}

async function deleteBoard() {
  if (!confirm("Board wirklich löschen?")) return

  await api.delete(`/boards/${boardId}`)
  router.push("/boards")
}

async function addMember() {
  if (!memberSelection.value) return

  await addBoardMember(boardId, memberSelection.value)
  await loadMembers()
}

async function removeMember(userId) {
  if (!confirm("Mitglied entfernen?")) return

  await removeBoardMember(boardId, userId)
  await loadMembers()
}

onMounted(async () => {
  await loadBoard()
  await loadMembers()
})

</script>

<template>
  <div class="board-edit">

    <h1>Board bearbeiten</h1>

    <div class="section">
      <label>Titel</label>
      <input v-model="title" />
      <button @click="saveTitle">Speichern</button>
    </div>

    <p v-if="error">{{ error }}</p>

    <hr />

    <div class="section">
      <h2>Mitglieder</h2>

      <div v-for="member in members" :key="member.id" class="member">
        {{ member.email }}

        <button
          v-if="member.id !== board.owner?.id"
          @click="removeMember(member.id)"
        >
          Entfernen
        </button>
      </div>

      <div v-if="availableMembers.length">
        <select v-model="memberSelection">
          <option
            v-for="m in availableMembers"
            :key="m.id"
            :value="m.id"
          >
            {{ m.email }}
          </option>
        </select>

        <button @click="addMember">
          Mitglied hinzufügen
        </button>
      </div>
    </div>

    <hr />

    <div class="section danger">
      <button @click="deleteBoard">
        Board löschen
      </button>
    </div>

    <router-link to="/boards">← Zurück</router-link>

  </div>
</template>

<style scoped>
.board-edit {
  max-width: 600px;
  margin: 2rem auto;
  padding: 1rem;
}

.section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  background: #fff;
}

input, select {
  padding: 8px;
  margin-right: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

button {
  padding: 6px 12px;
  margin-top: 5px;
  cursor: pointer;
}

.danger button {
  background: red;
  color: white;
}
</style>