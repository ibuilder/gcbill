php
<?php

namespace Tests;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUser()
    {
        $user = new User();
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hashed_password',
            'first_name' => 'Test',
            'last_name' => 'User',
            'role' => 'staff',
        ];
        $userId = $user->create($userData);

        $this->assertNotNull($userId);
    }

    public function testGetUser()
    {
        $user = new User();
        $userData = [
            'username' => 'testuser2',
            'email' => 'test2@example.com',
            'password_hash' => 'hashed_password',
            'first_name' => 'Test2',
            'last_name' => 'User2',
            'role' => 'staff',
        ];
        $userId = $user->create($userData);
        $retrievedUser = $user->find($userId);
        $this->assertEquals('testuser2', $retrievedUser['username']);
    }

    public function testUpdateUser()
    {
        $user = new User();
        $userData = [
            'username' => 'testuser3',
            'email' => 'test3@example.com',
            'password_hash' => 'hashed_password',
            'first_name' => 'Test3',
            'last_name' => 'User3',
            'role' => 'staff',
        ];
        $userId = $user->create($userData);
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ];
        $user->update($userId, $updateData);
        $updatedUser = $user->find($userId);
        $this->assertEquals('Updated', $updatedUser['first_name']);
        $this->assertEquals('Name', $updatedUser['last_name']);
    }

    public function testDeleteUser()
    {
        $user = new User();
        $userData = [
            'username' => 'testuser4',
            'email' => 'test4@example.com',
            'password_hash' => 'hashed_password',
            'first_name' => 'Test4',
            'last_name' => 'User4',
            'role' => 'staff',
        ];
        $userId = $user->create($userData);
        $user->delete($userId);
        $deletedUser = $user->find($userId);
        $this->assertNull($deletedUser);
    }
}