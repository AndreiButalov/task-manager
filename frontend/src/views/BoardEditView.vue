<script setup>
import { ref, onMounted, computed } from "vue"
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

const sortedMembers = computed(() => {
  if (!board.value) return members.value

  return [...members.value].sort((a, b) => {
    if (a.id === board.value.owner?.id) return -1
    if (b.id === board.value.owner?.id) return 1
    return 0
  })
})

onMounted(async () => {
  await loadBoard()
  await loadMembers()
})

</script>

<template>
  <div class="board-edit">

    <h1>Board bearbeiten</h1>

    <div class="title_edit section">
      <p>Board Name:</p>
      <div>
        <input v-model="title" />
        <button class="button_" @click="saveTitle">Speichern</button>
      </div>
    </div>

    <p v-if="error">{{ error }}</p>

    <hr />

    <div class="section">
      <h2>Mitglieder:</h2>

      <div v-for="member in sortedMembers" :key="member.id" class="member">
        {{ member.email }}

        <button
          class="button_"
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

        <button class="button_" @click="addMember">
          Mitglied hinzufügen
        </button>
      </div>
    </div>

    <hr />

    <div class="section footer">
      <button class="button_" @click="deleteBoard">
        Board löschen
      </button>
    </div>

  </div>
</template>

<style scoped>
.board-edit {
  max-width: 600px;
  margin: 2rem auto;
  padding: 1rem;
}

.title_edit {
  display: flex;
  justify-content: space-between;
  align-content: center;  
}

.title_edit p {
  font-weight: 700;
}

.title_edit div {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-right: 30px;
}

.title_edit input {
  width: 250px;
}

.button_ {
  color: black;
  border: #E0E0E0 solid 1px;
  background: transparent;
  padding: 10px 25px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 20px;
  cursor: pointer;
  margin: 0;
}

.button_:hover {
  color: #c82333;
  border: #c82333 solid 1px;
  background: #eca6ad;
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

.footer {
  display: flex;
  justify-content: center;
  align-items: center;
}

.member {
  margin: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>