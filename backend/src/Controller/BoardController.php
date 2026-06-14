<?php

namespace App\Controller;

use App\Repository\BoardRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class BoardController
{
    #[Route('/api/boards', methods: ['GET'])]
    public function index(BoardRepository $boardRepository): JsonResponse
    {
        $boards = $boardRepository->findAll();

        $data = [];

        foreach ($boards as $board) {
            $data[] = [
                'id' => $board->getId(),
                'title' => $board->getTitle(),
            ];
        }

        return new JsonResponse($data);
    }
}