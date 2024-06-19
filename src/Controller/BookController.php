<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Books;
use Doctrine\ORM\EntityManagerInterface;

class BookController extends AbstractController
{
    /**
     * @Route("/books", name="create_book", methods={"POST"})
     */
    public function createBook(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Validate request data
        $errors = $this->validateBookData($requestData);
        if ($errors) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Check if user with the same email already exists
        $existingBook = $entityManager->getRepository(Books::class)->findOneBy(['title' => $requestData['title'],'author' => $requestData['author'],'genere' => $requestData['genere']]);
        if ($existingBook !== null) {
            return new JsonResponse(['error' => 'Book with the same title, author, and genre already exists'], Response::HTTP_CONFLICT);
        }

        // Create new book
        $book = new Books();
        $book->setTitle($requestData['title']);
        $book->setAuthor($requestData['author']);
        $book->setGenere($requestData['genere']);
        $book->setIsbn($requestData['isbn']);
        $book->setPublishedDate(new \DateTime($requestData['published_date']));
        $book->setStatus('available');

        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Book created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/books/{id}", name="edit_book", methods={"PUT"})
     */
    public function editBook($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Find book by ID
        $book = $entityManager->getRepository(Books::class)->find($id);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate request data
        $errors = $this->validateBookData($requestData);
        if ($errors) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Check if the title, author, and genre are being changed to an existing book
        if (
            $book->getTitle() !== $requestData['title'] ||
            $book->getAuthor() !== $requestData['author'] ||
            $book->getGenere() !== $requestData['genere']
        ) {
            $existingBook = $entityManager->getRepository(Books::class)->findOneBy([
                'title' => $requestData['title'],
                'author' => $requestData['author'],
                'genere' => $requestData['genere']
            ]);
            if ($existingBook !== null) {
                return new JsonResponse(['error' => 'Book with the same title, author, and genre already exists'], Response::HTTP_CONFLICT);
            }
        }


        // Update book details
        $book->setTitle($requestData['title']);
        $book->setAuthor($requestData['author']);
        $book->setGenere($requestData['genere']);
        $book->setIsbn($requestData['isbn']);
        $book->setPublishedDate(new \DateTime($requestData['published_date']));
        //$book->setStatus($requestData['status']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Book updated successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     */
    public function deleteBook($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Find book by ID
        $book = $entityManager->getRepository(Books::class)->find($id);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Delete book
        $entityManager->remove($book);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Book deleted successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/books", name="list_books", methods={"GET"})
     */
    public function listBooks(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Retrieve all books
        $entityManager = $this->getDoctrine()->getManager();
        $books = $entityManager->getRepository(Books::class)->findAll();
        $bookArray = [];

        foreach ($books as $book) {
            $bookArray[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'genere' => $book->getGenere(),
                'isbn' => $book->getIsbn(),
                'published_date' => $book->getPublishedDate()->format('Y-m-d H:i:s'),
                'status' => $book->getStatus(),
            ];
        }

        return new JsonResponse($bookArray, Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="get_book_detail", methods={"GET"})
     */
    public function getBookDetail($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check authentication token
        if (!$this->isAuthenticated($requestData['AUTH_TOKEN'])) {
            return new JsonResponse(['error' => 'Invalid authentication token'], Response::HTTP_UNAUTHORIZED);
        }

        // Find book by ID
        $book = $entityManager->getRepository(Books::class)->find($id);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $bookArray = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'genere' => $book->getGenere(),
            'isbn' => $book->getIsbn(),
            'published_date' => $book->getPublishedDate()->format('Y-m-d H:i:s'),
            'status' => $book->getStatus(),
        ];

        return new JsonResponse($bookArray, Response::HTTP_OK);
    }

    private function isAuthenticated($authToken): bool
    {
        return isset($authToken) && $authToken === $_ENV['AUTH_TOKEN'];
    }

    private function validateBookData($requestData): array
    {
        $errors = [];

        if (!isset($requestData['title']) || empty($requestData['title'])) {
            $errors[] = 'Title is required';
        }

        if (!isset($requestData['author']) || empty($requestData['author'])) {
            $errors[] = 'Author is required';
        }

        if (!isset($requestData['genere']) || empty($requestData['genere'])) {
            $errors[] = 'Genere is required';
        }

        if (!isset($requestData['isbn']) || empty($requestData['isbn'])) {
            $errors[] = 'ISBN is required';
        }


        /*if (!isset($requestData['status']) || !in_array(strtolower($requestData['status']), ['available', 'borrowed'])) {
            $errors[] = 'Invalid status. Allowed status are available or borrowed';
        }*/

        if (isset($requestData['published_date']) && !empty($requestData['published_date']) && !\DateTime::createFromFormat('Y-m-d', $requestData['published_date'])) {
            $errors[] = 'Invalid published date format. Expected format: Y-m-d';
        }

        return $errors;
    }
}