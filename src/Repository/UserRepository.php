<?php

namespace App\Repository;

use App\Database\Connection;
use App\Entity\User;

class UserRepository
{
    public function __construct(private Connection $connection)
    {
    }
    
    /**
     * @param string $slug
     * @return User|null
     */
    public function findBySlug(string $slug): ?User
    {
        $database = $this->connection->getConnection();
        
        $statement = $database->prepare("SELECT id, slug, firstname, lastname, email, role, validated from user where slug = :slug ");
        $statement->bindParam(':slug', $slug);
        $statement->execute();
        $row = $statement->fetch();
        
        
        if ($row) {
            $user = $this->setUser($row);
        } else {
            $user = null;
        }
        
        return $user;
    }
    
    /**
     * @return array
     */
    public function findAll(): array
    {
        $database = $this->connection->getConnection();
        $statement = $database->prepare("SELECT id, slug, firstname, lastname, email, role, validated from user");
        $statement->execute();
        
        $users = [];
        while (($row = $statement->fetch())) {
            $user = $this->setUser($row);
            
            $users[] = $user;
        }
        
        return $users;
    }
    
    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $database = $this->connection->getConnection();
        $statement = $database->prepare('SELECT * from user where user.email = :email');
        $statement->bindParam(':email', $email);
        $statement->execute();
        
        $row = $statement->fetch();
        
        if ($row) {
            $user = $this->setUser($row);
        } else {
            $user = null;
        }
        
        return $user;
    }
    
    /**
     * @param User $user
     * @return bool
     */
    public function addUser(User $user): bool
    {
        
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $role = $user->getRole();
        $slug = $user->getSlug();
        $validated = $user->getValidated() ? 1 : 0;
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare('INSERT INTO user (firstname, lastname, email, password, role, slug, validated)
                            VALUES (:firstname, :lastname, :email, :password, :role, :slug, :validated)');
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':password', $password);
        $statement->bindParam(':role', $role);
        $statement->bindParam(':slug', $slug);
        $statement->bindParam(':validated', $validated);
        
        try {
            $statement->execute();
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        
        return true;
    }
    
    /**
     * @param mixed $row
     * @return User
     */
    private function setUser(mixed $row): User
    {
        $user = new User();
        $user->setSlug($row['slug']);
        $user->setEmail($row['email']);
        $user->setFirstname($row['firstname']);
        $user->setLastname($row['lastname']);
        $user->setRole($row['role']);
        $user->setValidated($row['validated']);
        $user->setId($row['id']);
        
        if (array_key_exists('password', $row)) {
            $user->setPassword($row['password']);
        }
        
        return $user;
    }
    
    /**
     * @param User $user
     * @return bool
     */
    public function updateUser(User $user): bool
    {
        
        $id = $user->getId();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $role = $user->getRole();
        $slug = $user->getSlug();
        $validated = $user->getValidated() ? 1 : 0;
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare('UPDATE user SET firstname = :firstname, lastname = :lastname, email = :email, password = :password, role = :role, slug = :slug, validated = :validated WHERE id = :id');
        $statement->bindParam(':id', $id);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':password', $password);
        $statement->bindParam(':role', $role);
        $statement->bindParam(':slug', $slug);
        $statement->bindParam(':validated', $validated);
        
        try {
            $statement->execute();
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function updateProfil(User $user): bool
    {
        $id = $user->getId();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $slug = $user->getSlug();
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare('UPDATE user SET firstname = :firstname, lastname = :lastname, email = :email, slug = :slug WHERE id = :id');
        $statement->bindParam(':id', $id);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':slug', $slug);
        
        try {
            $statement->execute();
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function updatePassword(User $user, mixed $password): bool
    {
        
        $id = $user->getId();
        $database = $this->connection->getConnection();
        $statement = $database->prepare('UPDATE user SET password = :password WHERE id = :id');
        $statement->bindParam(':id', $id);
        $statement->bindParam(':password', $password);
        
        try {
            $statement->execute();
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        
        return true;
        
    }
    
    public function updateRole(User $user): bool
    {
        $id = $user->getId();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();
        $email = $user->getEmail();
        $slug = $user->getSlug();
        $role = $user->getRole();
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare('UPDATE user SET role = :role, firstname = :firstname, lastname = :lastname, email = :email, slug = :slug WHERE id = :id');
        $statement->bindParam(':id', $id);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':slug', $slug);
        $statement->bindParam(':role', $role);
        
        try {
            $statement->execute();
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function delete(User $user): bool
    {
        
        $id = $user->getId();
        $database = $this->connection->getConnection();
        $statement = $database->prepare('DELETE FROM user WHERE id = :id');
        $statement->bindParam(':id', $id);
        
        try {
            $statement->execute();
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        return true;
    }
}