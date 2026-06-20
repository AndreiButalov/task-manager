import api from './api';

export async function loadBoards() {
  const response = await api.get('/boards');
  return response.data;
}

export async function loadBoard(id) {
  const response = await api.get(`/boards/${id}`);
  return response.data;
}

export async function createTask(taskListId, title, description = '', position = 0) {
  const response = await api.post('/tasks', {
    title,
    description,
    position,
    task_list_id: taskListId,
  });

  return response.data;
}

export async function createTaskList(boardId, title, position) {
  const response = await api.post('/tasklists', {
    title,
    position,
    board_id: boardId,
  });

  return response.data;
}

export async function loadBoardMembers(boardId) {
  const response = await api.get(`/boards/${boardId}/members`);
  return response.data;
}

export async function loadAvailableMembers(boardId) {
  const response = await api.get(`/boards/${boardId}/available-members`);
  return response.data;
}

export async function addBoardMember(boardId, userId) {
  const response = await api.post(`/boards/${boardId}/members`, { user_id: userId });
  return response.data;
}

export async function removeBoardMember(boardId, userId) {
  const response = await api.delete(`/boards/${boardId}/members/${userId}`);
  return response.data;
}

export async function updateTask(id, data) {
  const response = await api.put(`/tasks/${id}`, data);
  return response.data;
}

export async function deleteTask(id) {
  const response = await api.delete(`/tasks/${id}`);
  return response.data;
}


export async function loadTaskLists() {
  const response = await api.get('/tasklists');
  return response.data;
}

export async function loadProfile() {
  const { data } = await api.get("/me");

  return {
    firstName: data.firstName || "",
    lastName: data.lastName || "",
    email: data.email || "",
  };
}

export function formatMemberName(member) {
  const user = member.user ?? member;

  const fullName = `${user.firstName ?? ""} ${user.lastName ?? ""}`.trim();

  return fullName || user.email || "Unbekannt";
}