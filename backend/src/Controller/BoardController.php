<?php

namespace App\Controller;

use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class BoardController
{
    #[Route('/api/boards', name: 'api_boards_get', methods: ['GET'])]
    public function index(BoardRepository $boardRepository): JsonResponse
    {
        $boards = $boardRepository->findAll();

        $data = [];

        foreach ($boards as $board) {
            $data[] = [
                'id' => $board->getId(),
                'title' => $board->getTitle(),
                'createdAt' => $board->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/boards', name: 'api_boards_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;

        if (!$title) {
            return new JsonResponse([
                'error' => 'Title is required'
            ], 400);
        }

        $board = new Board();
        $board->setTitle($title);
        $board->setCreatedAt(new \DateTimeImmutable());

        // 🔥 FIX: Owner setzen (vorübergehend User ID 3)
        $user = $userRepository->find(3);

        if (!$user) {
            return new JsonResponse([
                'error' => 'User not found (ID 3 missing)'
            ], 500);
        }

        $board->setOwner($user);

        $em->persist($board);
        $em->flush();

        return new JsonResponse([
            'message' => 'Board created successfully',
            'id' => $board->getId(),
            'title' => $board->getTitle()
        ]);
    }
}