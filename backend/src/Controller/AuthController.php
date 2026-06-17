<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $repeatPassword = $data['repeat_password'] ?? null;

        if (!$email || !$password || !$repeatPassword) {
            return new JsonResponse([
                'error' => 'Email, password and repeat_password are required'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => 'Invalid email format'
            ], 400);
        }

        if ($password !== $repeatPassword) {
            return new JsonResponse([
                'error' => 'Passwords do not match'
            ], 400);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse([
                'error' => 'User already exists'
            ], 400);
        }

        $user = new User();
        $user->setEmail($email);

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'User created successfully'
        ]);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse([
                'error' => 'Email and password are required'
            ], 400);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'error' => 'Invalid credentials'
            ], 401);
        }

        return new JsonResponse([
            'token' => $jwtManager->create($user),
            'user' => [
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }
}
