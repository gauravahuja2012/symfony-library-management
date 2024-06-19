<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;
use App\Entity\Books;
use App\Entity\Borrows;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class BorrowController extends AbstractController
{
    /**
     * @Route("/borrow/book", name="borrow_book", methods={"POST"})
     */
    public function borrowBook(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $entityManager->getRepository(Users::class)->find($requestData['user_id']);
        $book = $entityManager->getRepository(Books::class)->find($requestData['book_id']);

        if (!$user || !$book) {
            return new JsonResponse(['error' => 'User or Book not found'], Response::HTTP_NOT_FOUND);

        }

        // Check if the book is available for borrowing
        if (!$book->isAvailable()) {
            return new JsonResponse(['error' => 'Book not available'], Response::HTTP_BAD_REQUEST);
        }

        // Create a new borrow record
        $borrow = new Borrows();
        $borrow->setUser($user);
        $borrow->setBook($book);
        $borrow->setBorrowDate(new \DateTime());

        //Update book status to borrowed
        $book->setStatus('borrowed');

        // Persist the changes
        $entityManager->persist($borrow);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Book borrowed successfully']);
    }

    /**
     * @Route("/return/book", name="return_book", methods={"POST"})
     */
    public function returnBook(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $entityManager->getRepository(Users::class)->find($requestData['user_id']);
        $book = $entityManager->getRepository(Books::class)->find($requestData['book_id']);

        if (!$user || !$book) {
            return new JsonResponse(['error' => 'User or Book not found'], Response::HTTP_NOT_FOUND);
        }
        // Find the borrow record
        $borrow = $entityManager->getRepository(Borrows::class)->findOneBy([
            'user' => $user,
            'book' => $book,
            'returnDate' => null // Check if the book is already returned
        ]);

        if (!$borrow) {
            return new JsonResponse(['error' => 'Borrow record not found'], Response::HTTP_NOT_FOUND);
        }

        // Set the return date
        $borrow->setReturnDate(new \DateTime());

        // Update book status to available
        $book->setStatus('available');

        // Persist the changes
        $entityManager->flush();

        return new JsonResponse(['message' => 'Book returned successfully']);
    }

    /**
     * @Route("/borrow/history", name="borrow_history", methods={"GET"})
     */
    public function viewBorrowingHistory(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }
        $user = $entityManager->getRepository(Users::class)->find($requestData['user_id']);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Fetch borrowing history for the user
        $borrows = $user->getBorrows();

        // Format borrowing history data
        $history = [];
        foreach ($borrows as $borrow) {
            $history[] = [
                'book_title' => $borrow->getBook()->getTitle(),
                'borrow_date' => $borrow->getBorrowDate()->format('Y-m-d H:i:s'),
                'return_date' => $borrow->getReturnDate() ? $borrow->getReturnDate()->format('Y-m-d H:i:s') : null
            ];
        }

        return new JsonResponse($history);
    }

    private function isAuthenticated($authToken): bool
    {
        return isset($authToken) && $authToken === $_ENV['AUTH_TOKEN'];
    }
}