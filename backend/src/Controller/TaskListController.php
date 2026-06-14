<?php

namespace App\Controller;

use App\Repository\TaskListRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TaskListController
{
    #[Route('/api/tasklists', name: 'api_tasklists', methods: ['GET'])]
    public function index(TaskListRepository $taskListRepository): JsonResponse
    {
        $lists = $taskListRepository->findAll();

        $data = [];

        foreach ($lists as $list) {
            $data[] = [
                'id' => $list->getId(),
                'title' => $list->getTitle(),
                'position' => $list->getPosition(),
                'board_id' => $list->getBoard()?->getId(),
            ];
        }

        return new JsonResponse($data);
    }
}