<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
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
}
