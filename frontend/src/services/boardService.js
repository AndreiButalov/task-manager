import api from './api';

export async function loadBoards() {
  const response = await api.get('/boards');
  return response.data;
}

export async function loadBoard(id) {
  const response = await api.get(`/boards/${id}`);
  return response.data;
}
