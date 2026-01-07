<?php

namespace App\Dummy;

class UserRepo
{

    public function getUserById(int $id)
    {
        return [
            'id' => $id,
            'name' => 'John',
            'email' => 'john@doe.com',
            'telenumber' => '0123456789'
        ];
    }

    public function getAllUsers()
    {
        return [
            [
                'id' => 1,
                'name' => 'John',
                'email' => 'john@doe.com',
                'telenumber' => '0123456789'
            ],
            [
                'id' => 2,
                'name' => 'Jane',
                'email' => 'jane@doe.com',
                'telenumber' => '0987654321'
            ]
        ];
    }
}