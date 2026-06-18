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
