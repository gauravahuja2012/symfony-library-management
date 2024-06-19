<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BookControllerTest extends WebTestCase
{
    private $authToken = 'asndkweweknwkcdfdfdokadksrer';

    public function testCreateBook()
    {
        $client = static::createClient();

        // Send a request to create a new book
        $client->request(
            'POST',
            '/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Test Book',
                'author' => 'Test Author',
                'isbn' => '978-3-16-148410-0',
                'published_date' => '2023-06-01',
                'AUTH_TOKEN' => $this->authToken
            ])
        );

        // Assert response status code is 201 (Created)
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    public function testEditBook()
    {
        $client = static::createClient();

        // Send a request to edit an existing book
        $client->request(
            'PUT',
            '/books/2', // Replace {id} with the actual ID of the book you want to edit
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Updated Test Book',
                'author' => 'Updated Test Author',
                'isbn' => '978-3-16-148410-1',
                'published_date' => '2023-07-01',
                'AUTH_TOKEN' => $this->authToken
            ])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testDeleteBook()
    {
        $client = static::createClient();

        // Get the last book ID from the database
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $query = $entityManager->createQuery('SELECT MAX(b.id) FROM App\Entity\Books b');
        $lastBookId = $query->getSingleScalarResult();

        // Send a request to delete an existing book
        $client->request(
            'DELETE',
            '/books/'.$lastBookId, // Replace {id} with the actual ID of the book you want to delete
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['AUTH_TOKEN' => $this->authToken])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testListBooks()
    {
        $client = static::createClient();

        // Send a request to list all books
        $client->request(
            'GET',
            '/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['AUTH_TOKEN' => $this->authToken])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testGetBookDetail()
    {
        $client = static::createClient();

        // Send a request to get details of a specific book
        $client->request(
            'GET',
            '/books/2', // Replace {id} with the actual ID of the book you want to get details of
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['AUTH_TOKEN' => $this->authToken])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}