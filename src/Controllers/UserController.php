<?php
namespace App\Controllers;
class UserController
{

    public function index(): string {
        return "Welcome to user List";
    }
    public function show(array $params): string
    {
        $userId = $params['id'];
        $userId = filter_var($params['id'], FILTER_VALIDATE_INT);

        if($userId==false) {
            return "Invalid Users";
        }
        return "Showing user profile for ID: {$userId}";
    }
}