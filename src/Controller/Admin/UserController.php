<?php

namespace App\Controller\Admin;

use App\Database\Connection;
use App\Entity\User;
use App\Repository\UserRepository;

class UserController
{
    public function index()
    {
        $connection = new Connection();
        $users = (new UserRepository($connection))->findAll();
        var_dump($users);
        die();
    }
    
    public function edit(string $slug)
    {
        $connection = new Connection();
        $user = (new UserRepository($connection))->find($slug);
        
        var_dump($user);
        die();
    }
}