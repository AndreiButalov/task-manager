<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\User;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class BoardController
{
    #[Route('/api/boards', name: 'api_boards_get', methods: ['GET'])]
    public function index(
        BoardRepository $boardRepository,
        #[CurrentUser] ?User $currentUser
    ): JsonResponse {
        if (!$currentUser) {
            return new JsonResponse([
                'error' => 'User not authenticated'
            ], 401);
        }

        $boards = $boardRepository->findAll();

        $data = [];

        foreach ($boards as $board) {
            $isOwner = $board->getOwner()?->getId() === $currentUser->getId();
            $isMember = $board->getMembers()->exists(
                fn($key, $member) => $member->getId() === $currentUser->getId()
            );

            if ($isOwner || $isMember) {
                $memberIds = array_map(
                    fn($member) => $member->getId(),
                    $board->getMembers()->toArray()
                );

                $data[] = [
                    'id' => $board->getId(),
                    'title' => $board->getTitle(),
                    'createdAt' => $board->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'owner' => [
                        'id' => $board->getOwner()?->getId(),
                        'email' => $board->getOwner()?->getEmail(),
                    ],
                    'memberIds' => $memberIds,
                ];
            }
        }

        return new JsonResponse($data);
    }

    #[Route('/api/boards', name: 'api_boards_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $currentUser
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;

        if (!$title) {
            return new JsonResponse([
                'error' => 'Title is required'
            ], 400);
        }

        if (!$currentUser) {
            return new JsonResponse([
                'error' => 'User not authenticated'
            ], 401);
        }

        $board = new Board();
        $board->setTitle($title);
        $board->setCreatedAt(new \DateTimeImmutable());

        $board->setOwner($currentUser);
        $board->addMember($currentUser);

        $em->persist($board);
        $em->flush();

        return new JsonResponse([
            'message' => 'Board created successfully',
            'id' => $board->getId(),
            'title' => $board->getTitle()
        ]);
    }

    #[Route('/api/boards/{id}/members', name: 'api_boards_members_get', methods: ['GET'])]
    public function members(
        int $id,
        BoardRepository $boardRepository
    ): JsonResponse {
        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
        }

        $data = [];

        foreach ($board->getMembers() as $member) {
            $data[] = [
                'id' => $member->getId(),
                'email' => $member->getEmail(),
                'firstName' => $member->getFirstName(),
                'lastName' => $member->getLastName(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/boards/{id}/available-members', name: 'api_boards_available_members', methods: ['GET'])]
    public function availableMembers(
        int $id,
        BoardRepository $boardRepository,
        UserRepository $userRepository
    ): JsonResponse {
        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
        }

        $memberIds = array_map(
            fn($member) => $member->getId(),
            $board->getMembers()->toArray()
        );

        $available = [];

        foreach ($userRepository->findAll() as $user) {
            if ($user->getId() === $board->getOwner()?->getId()) {
                continue;
            }

            if (!in_array($user->getId(), $memberIds, true)) {
                $available[] = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ];
            }
        }

        return new JsonResponse($available);
    }

    #[Route('/api/boards/{id}/members', name: 'api_boards_members_add', methods: ['POST'])]
    public function addMember(
        int $id,
        Request $request,
        BoardRepository $boardRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
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

        if ($board->getOwner()?->getId() === $user->getId()) {
            return new JsonResponse(['error' => 'Owner is already part of the board'], 400);
        }

        if ($board->getMembers()->contains($user)) {
            return new JsonResponse(['error' => 'User is already a member'], 400);
        }

        $board->addMember($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'Member added',
            'user_id' => $user->getId(),
        ]);
    }

    #[Route('/api/boards/{id}/members/{userId}', name: 'api_boards_members_remove', methods: ['DELETE'])]
    public function removeMember(
        int $id,
        int $userId,
        BoardRepository $boardRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        if (!$board->getMembers()->contains($user)) {
            return new JsonResponse(['error' => 'User is not a board member'], 400);
        }

        $board->removeMember($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'Member removed',
            'user_id' => $user->getId(),
        ]);
    }

    #[Route('/api/boards/{id}', name: 'api_boards_detail', methods: ['GET'])]
    public function show(
        int $id,
        BoardRepository $boardRepository,
        #[CurrentUser] ?User $currentUser
    ): JsonResponse {
        if (!$currentUser) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
        }

        $isOwner = $board->getOwner()?->getId() === $currentUser->getId();
        $isMember = $board->getMembers()->exists(
            fn($key, $member) => $member->getId() === $currentUser->getId()
        );

        if (!$isOwner && !$isMember) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $taskLists = $board->getTaskLists()->toArray();
        usort($taskLists, fn($a, $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

        foreach ($taskLists as $taskList) {
            $tasks = $taskList->getTasks()->toArray();
            usort($tasks, fn($a, $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

            $taskData = [];
            foreach ($tasks as $task) {
                $taskData[] = [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'position' => $task->getPosition(),
                    'task_list_id' => $task->getTaskList()?->getId(),
                    'createdAt' => $task->getCreatedAt()?->format('Y-m-d H:i:s'),
                    'dueDate' => $task->getDueDate()?->format('Y-m-d H:i:s'),
                ];
            }

            $taskLists[] = [
                'id' => $taskList->getId(),
                'title' => $taskList->getTitle(),
                'position' => $taskList->getPosition(),
                'tasks' => $taskData,
            ];
        }

        $memberIds = array_map(
            fn($member) => $member->getId(),
            $board->getMembers()->toArray()
        );

        return new JsonResponse([
            'id' => $board->getId(),
            'title' => $board->getTitle(),
            'createdAt' => $board->getCreatedAt()?->format('Y-m-d H:i:s'),
            'owner' => [
                'id' => $board->getOwner()?->getId(),
                'email' => $board->getOwner()?->getEmail(),
            ],
            'memberIds' => $memberIds,
            'taskLists' => $taskLists,
        ]);
    }

    #[Route('/api/boards/{id}', name: 'api_boards_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        BoardRepository $boardRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? null;

        if (!$title) {
            return new JsonResponse(['error' => 'Title is required'], 400);
        }

        $board->setTitle($title);

        $em->flush();

        return new JsonResponse([
            'message' => 'Board updated',
            'id' => $board->getId(),
            'title' => $board->getTitle()
        ]);
    }


    #[Route('/api/boards/{id}', name: 'api_boards_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        BoardRepository $boardRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $board = $boardRepository->find($id);

        if (!$board) {
            return new JsonResponse(['error' => 'Board not found'], 404);
        }

        $em->remove($board);
        $em->flush();

        return new JsonResponse(['message' => 'Board deleted']);
    }
}