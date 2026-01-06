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
}