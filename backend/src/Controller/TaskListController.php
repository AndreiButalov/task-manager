<?php

namespace App\Controller;

use App\Repository\TaskListRepository;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\BoardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TaskListController
{
    #[Route('/api/tasklists', name: 'api_tasklists', methods: ['GET'])]
    public function index(
        TaskListRepository $taskListRepository,
        #[CurrentUser] ?User $currentUser
    ): JsonResponse
    {
        if (!$currentUser) {
            return new JsonResponse([
                'error' => 'User not authenticated'
            ], 401);
        }

        $lists = $taskListRepository->findAll();

        $data = [];

        foreach ($lists as $list) {
            $board = $list->getBoard();
            
            if (!$board) {
                continue;
            }
            
            $isOwner = $board->getOwner()?->getId() === $currentUser->getId();
            $isMember = $board->getMembers()->exists(
                fn ($key, $member) => $member->getId() === $currentUser->getId()
            );

            if ($isOwner || $isMember) {
                $data[] = [
                    'id' => $list->getId(),
                    'title' => $list->getTitle(),
                    'position' => $list->getPosition(),
                    'board_id' => $list->getBoard()?->getId(),
                ];
            }
        }

        return new JsonResponse($data);
    }


    #[Route('/api/tasklists', name: 'api_tasklists_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        BoardRepository $boardRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;
        $position = $data['position'] ?? 0;
        $boardId = $data['board_id'] ?? null;

        if (!$title || !$boardId) {
            return new JsonResponse([
                'error' => 'title and board_id are required'
            ], 400);
        }

        $board = $boardRepository->find($boardId);

        if (!$board) {
            return new JsonResponse([
                'error' => 'Board not found'
            ], 404);
        }

        $taskList = new TaskList();
        $taskList->setTitle($title);
        $taskList->setPosition($position);
        $taskList->setBoard($board);

        $em->persist($taskList);
        $em->flush();

        return new JsonResponse([
            'message' => 'TaskList created',
            'id' => $taskList->getId(),
            'title' => $taskList->getTitle()
        ]);
    }
}