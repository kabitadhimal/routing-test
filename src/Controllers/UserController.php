<?php
namespace App\Controllers;
use App\Dummy\UserRepo;

class UserController
{

    public function __construct(

    )
    {

    }

    public function index(): string {
        return "Welcome to user List";
    }
    public function show(
        int $id, string $mode,
        UserRepo $userRepo
    ): string
    {
        //var_dump($mode, $id);
        var_dump($userRepo->getUserById($id));
        //$userId = $params['id'];
        //$userId = filter_var($params['id'], FILTER_VALIDATE_INT);

        //if($userId==false) {
          //  return "Invalid Users";
        //}
        return "Showing user profile for ID: {$id}";
    }
}