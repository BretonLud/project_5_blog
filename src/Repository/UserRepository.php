<?php

namespace App\Repository;

use App\Database\Connection;
use App\Entity\User;

class UserRepository
{
    public function __construct(private Connection $connection)
    {
    }
    
    public function findBySlug(string $slug): ?User
    {
        $database = $this->connection->getConnection();
        
        $statement = $database->prepare("SELECT id, slug, firstname, lastname, email, role, validated from user where slug = :slug ");
        $statement->bindParam(':slug', $slug);
        $statement->execute();
        $row = $statement->fetch();
        
        
        if ($row) {
            $user = $this->getUser($row);
        } else {
            $user = null;
        }
        
        return $user;
    }
    
    public function findAll(): array
    {
        $database = $this->connection->getConnection();
        $statement = $database->prepare("SELECT id, slug, firstname, lastname, email, role, validated from user");
        $statement->execute();
        
        $users = [];
        while (($row = $statement->fetch())) {
            $user = $this->getUser($row);
            
            $users[] = $user;
        }
        
        return $users;
    }
    
    /**
     * @param mixed $row
     * @return User
     */
    private function getUser(mixed $row): User
    {
        $user = new User();
        $user->setSlug($row['slug']);
        $user->setEmail($row['email']);
        $user->setFirstname($row['firstname']);
        $user->setLastname($row['lastname']);
        $user->setRole($row['role']);
        $user->setValidated($row['validated']);
        $user->setId($row['id']);
        return $user;
    }
}