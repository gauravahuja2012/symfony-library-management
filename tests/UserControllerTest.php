<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private $authToken = 'asndkweweknwkcdfdfdokadksrer';

    public function testCreateUser()
    {
        $client = static::createClient();

        // Send a request to create a new user
        $client->request(
            'POST',
            '/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test User',
                'email' => 'test'.rand(0,9999999999).'@example.com',
                'password' => 'password',
                'role' => 'admin',
                'AUTH_TOKEN' => $this->authToken
            ])
        );

        // Assert response status code is 201 (Created)
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    public function testEditUser()
    {
        $client = static::createClient();

        // Send a request to edit an existing user
        $client->request(
            'PUT',
            '/users/2', // Replace {id} with the actual ID of the user you want to edit
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Updated Test User',
                'email' => 'updated_test@example.com',
                'role' => 'member',
                'password' => "testing123",
                'AUTH_TOKEN' => $this->authToken
            ])
        );
        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testDeleteUser()
    {
        $client = static::createClient();

        // Get the last user ID from the database
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $query = $entityManager->createQuery('SELECT MAX(u.id) FROM App\Entity\Users u');
        $lastUserId = $query->getSingleScalarResult();

        // Send a request to delete an existing user
        $client->request(
            'DELETE',
            '/users/'.$lastUserId, // Replace {id} with the actual ID of the user you want to delete
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['AUTH_TOKEN' => $this->authToken])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testListUsers()
    {
        $client = static::createClient();

        // Send a request to list all users
        $client->request(
            'GET',
            '/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['AUTH_TOKEN' => $this->authToken])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testGetUserDetail()
    {
        $client = static::createClient();

        // Send a request to get details of a specific user
        $client->request(
            'GET',
            '/users/2', // Replace {id} with the actual ID of the user you want to get details of
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['AUTH_TOKEN' => $this->authToken])
        );

        // Assert response status code is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}