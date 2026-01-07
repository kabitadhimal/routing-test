<?php
namespace App\Controllers;

use App\Core\View;
use App\Dummy\UserRepo;

class UserController
{

    public function __construct() {

    }

    public function index(): string {
        return "Welcome to user List";
    }

    public function edit(
        int $id, string $mode,
        UserRepo $userRepo
    ): string
    {
    
        return View::render('users/edit', [
            'user' => $userRepo->getUserById($id),
            'mode' => $mode
        ]);

    }

    public function listing(
        UserRepo $userRepo
    ): string
    {
    
        return View::render('users/list', [
            'users' => $userRepo->getAllUsers()
        ]);

    }
}