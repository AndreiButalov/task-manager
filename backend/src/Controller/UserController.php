<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Nicht angemeldet'
            ], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ]);
    }

    #[Route('/me', name: 'api_me_update', methods: ['PUT'])]
    public function updateMe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Nicht angemeldet'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $user->setFirstName($data['firstName'] ?? '');
        $user->setLastName($data['lastName'] ?? '');

        $em->flush();

        return $this->json([
            'message' => 'Profil aktualisiert',
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ]);
    }
}
