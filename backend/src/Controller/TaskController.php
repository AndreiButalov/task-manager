<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


final class TaskController
{
    #[Route('/api/tasks', name: 'api_tasks_get', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();

        $data = [];

        foreach ($tasks as $task) {
            $data[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'position' => $task->getPosition(),
                'task_list_id' => $task->getTaskList()?->getId(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/tasks', name: 'api_tasks_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        TaskListRepository $taskListRepository
    ): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $title = $data['title'] ?? null;
    $description = $data['description'] ?? null;
    $position = $data['position'] ?? 0;
    $taskListId = $data['task_list_id'] ?? null;

    if (!$title || !$taskListId) {
        return new JsonResponse([
            'error' => 'title and task_list_id are required'
        ], 400);
    }

    $taskList = $taskListRepository->find($taskListId);

    if (!$taskList) {
        return new JsonResponse([
            'error' => 'TaskList not found'
        ], 404);
    }

    $task = new Task();
    $task->setTitle($title);
    $task->setDescription($description);
    $task->setPosition($position);
    $task->setCreatedAt(new \DateTimeImmutable());
    $task->setTaskList($taskList);

    $em->persist($task);
    $em->flush();

    return new JsonResponse([
        'message' => 'Task created',
        'id' => $task->getId(),
        'title' => $task->getTitle()
    ]);
    }

    #[Route('/api/tasks/{id}/available-assignees', name: 'api_tasks_available_assignees', methods: ['GET'])]
    public function availableAssignees(
        int $id,
        TaskRepository $taskRepository
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $board = $task->getTaskList()?->getBoard();

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found for this task'], 404);
        }

        $data = [];

        foreach ($board->getMembers() as $member) {
            $data[] = [
                'id' => $member->getId(),
                'email' => $member->getEmail(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/tasks/{id}/assignees', name: 'api_tasks_add_assignee', methods: ['POST'])]
    public function addAssignee(
        int $id,
        Request $request,
        TaskRepository $taskRepository,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            return new JsonResponse(['error' => 'user_id is required'], 400);
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $board = $task->getTaskList()?->getBoard();

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found for this task'], 404);
        }

        if (!$board->getMembers()->contains($user)) {
            return new JsonResponse(['error' => 'User is not a member of the board'], 400);
        }

        if ($task->getAssignees()->contains($user)) {
            return new JsonResponse(['error' => 'User is already assigned'], 400);
        }

        $task->addAssignee($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'Assignee added',
            'user_id' => $user->getId(),
        ]);
    }

    #[Route('/api/tasks/{id}', name: 'api_tasks_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        TaskRepository $taskRepository,
        TaskListRepository $taskListRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['position'])) {
            $task->setPosition((int) $data['position']);
        }

        if (isset($data['task_list_id'])) {
            $taskList = $taskListRepository->find($data['task_list_id']);
            if (!$taskList) {
                return new JsonResponse(['error' => 'TaskList not found'], 404);
            }
            $task->setTaskList($taskList);
        }

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Task updated']);
    }

    #[Route('/api/tasks/{id}', name: 'api_tasks_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        TaskRepository $taskRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $em->remove($task);
        $em->flush();

        return new JsonResponse(['message' => 'Task deleted']);
    }
}
