<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BorrowControllerTest extends WebTestCase
{
    private $authToken = 'asndkweweknwkcdfdfdokadksrer';

    public function testBorrowBook()
    {
        $client = static::createClient();

        // Send a request to borrow a book
        $client->request(
            'POST',
            '/borrow/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => 1, // Replace with the actual user ID
                'book_id' => 1, // Replace with the actual book ID
                'AUTH_TOKEN' => $this->authToken
            ])
        );

        // Assert response status code is 200 (OK) or 201 (Created) based on your implementation
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        // $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    public function testReturnBook()
    {
        $client = static::createClient();

        // Send a request to return a book
        $client->request(
            'POST',
            '/return/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => 1, // Replace with the actual user ID
                'book_id' => 1, // Replace with the actual book ID
                'AUTH_TOKEN' => $this->authToken
            ])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testViewBorrowingHistory()
    {
        $client = static::createClient();

        // Send a request to view borrowing history
        $client->request(
            'GET',
            '/borrow/history',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => 1, // Replace with the actual user ID
                'AUTH_TOKEN' => $this->authToken
            ])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}