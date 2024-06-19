<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="create_user", methods={"POST"})
     */
    public function createUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate request data
        $errors = $this->validateUserData($requestData);
        if ($errors) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Check if user with the same email already exists
        $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['email' => $requestData['email'],'role' => $requestData['role']]);
        if ($existingUser !== null) {
            return new JsonResponse(['error' => 'User with the same email already exists'], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new Users();
        $user->setName($requestData['name']);
        $user->setEmail($requestData['email']);
        $user->setRole($requestData['role']);
        $user->setPassword($requestData['password']);
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/users/{id}", name="edit_user", methods={"PUT"})
     */
    public function editUser($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Find user by ID
        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate request data
        $errors = $this->validateUserData($requestData);
        if ($errors) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Check if the email is being changed to an existing email
        if ($user->getEmail() !== $requestData['email']) {
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['email' => $requestData['email'],'role' => $requestData['role']]);
            if ($existingUser !== null) {
                return new JsonResponse(['error' => 'User with the same email already exists'], Response::HTTP_CONFLICT);
            }
        }

        // Update user details
        $user->setName($requestData['name']);
        $user->setEmail($requestData['email']);
        $user->setRole($requestData['role']);
        // If password is provided, update it
        if (isset($requestData['password'])) {
            $user->setPassword($requestData['password']);
        }
        $user->setUpdatedAt(new \DateTime());

        $entityManager->flush();

        return new JsonResponse(['message' => 'User updated successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/users/{id}", name="delete_user", methods={"DELETE"})
     */
    public function deleteUser($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Check authentication token
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }


        // Find user by ID
        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Delete user
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/users", name="list_users", methods={"GET"})
     */
    public function listUsers(Request $request): JsonResponse
    {
        // Check authentication token
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }


        // Retrieve all users
        $entityManager = $this->getDoctrine()->getManager();
        $users = $entityManager->getRepository(Users::class)->findAll();
        $userArray = [];

        foreach ($users as $user) {
            $userArray[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($userArray, Response::HTTP_OK);
    }

    /**
     * @Route("/users/{id}", name="get_user_detail", methods={"GET"})
     */
    public function getUserDetail($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Check authentication token
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Find user by ID
        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $userArray = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($userArray, Response::HTTP_OK);
    }


    private function isAuthenticated($authToken): bool
    {
        return isset($authToken) && $authToken === $_ENV['AUTH_TOKEN'];
    }

    private function validateUserData($requestData): array
    {
        $errors = [];

        if (!isset($requestData['name']) || empty($requestData['name'])) {
            $errors[] = 'Name is required';
        }

        if (!isset($requestData['email']) || empty($requestData['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($requestData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (!isset($requestData['password']) || empty($requestData['password'])) {
            $errors[] = 'Password is required';
        }

        if (!isset($requestData['role']) || !in_array(strtolower($requestData['role']), ['admin', 'member'])) {
            $errors[] = 'Invalid role. Allowed roles are admin or member';
        }

        return $errors;
    }
}