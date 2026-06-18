import api from './api';

export async function loadBoards() {
  const response = await api.get('/boards');
  return response.data;
}
