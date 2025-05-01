<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testHashPassword()
    {
        // Test the hashPassword method
        $password = 'testpassword';
        $hashedPassword = Auth::hashPassword($password);
        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($password, $hashedPassword);
    }

    public function testCheckPassword()
    {
        // Test the checkPassword method
        $password = 'testpassword';
        $hashedPassword = Auth::hashPassword($password);
        $this->assertTrue(Auth::checkPassword($password, $hashedPassword));
        $this->assertFalse(Auth::checkPassword('wrongpassword', $hashedPassword));
    }

    public function testCheckPermission()
    {
        // Test the checkPermission method
        // You will need to create a mock User object and set up the database for this test
        // Example:
        //$user = new User();
        //$user->role = 'admin';
        //$this->assertTrue(Auth::checkPermission($user, 'SomeController', 'someAction'));
    }

    public function testLogin()
    {
        // Test the login method
        // You will need to create a mock User object and set up the database for this test
        // Example:
        //$user = new User();
        //$user->username = 'testuser';
        //$user->password_hash = Auth::hashPassword('testpassword');
        //$this->assertTrue(Auth::login('testuser', 'testpassword'));
    }

    public function testIsLoggedIn()
    {
        // Test the isLoggedIn method
        // You will need to simulate a logged in user for this test
        // Example:
        //$_SESSION['user'] = 'someUser';
        //$this->assertTrue(Auth::isLoggedIn());
    }
}