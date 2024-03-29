<?php

namespace App\Repository;

use App\Database\Connection;
use App\Entity\Picture;
use Exception;
use PDO;

class PictureRepository
{
    private Connection $connection;
    private PDO $database;
    
    public function __construct()
    {
        $this->connection = new Connection();
        $this->database = $this->connection->getConnection();
    }
    
    public function delete(Picture $picture): void
    {
        $query = "DELETE FROM picture WHERE id = :id";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':id', $picture->getId());
        $statement->execute();
    }
    
    public function createImageForBlog(Picture $picture): void
    {
        $query = "INSERT INTO picture (blog_id, created_at, name, header, slug) VALUES (:blog_id, :created_at, :name, :header, :slug)";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':blog_id', $picture->getBlog()->getId());
        $statement->bindValue(':created_at', $picture->getCreatedAt()->format('Y-m-d H:i:s'));
        $statement->bindValue(':name', $picture->getName());
        $statement->bindValue(':header', (int)$picture->getHeader(), \PDO::PARAM_INT);
        $statement->bindValue(':slug', $picture->getSlug());
        $statement->execute();
    }
    
    public function updateHeader(Picture $picture): void
    {
        $query = "UPDATE picture SET header = :header WHERE id = :id";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':header', (int)$picture->getHeader(), \PDO::PARAM_INT);
        $statement->bindValue(':id', $picture->getId());
        $statement->execute();
    }
    
    public function update(Picture $picture): void
    {
        $query = "UPDATE picture SET created_at = :created_at, name = :name, header = :header, slug = :slug WHERE id = :id";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':created_at', $picture->getCreatedAt()->format('Y-m-d H:i:s'));
        $statement->bindValue(':name', $picture->getName());
        $statement->bindValue(':header', (int)$picture->getHeader(), \PDO::PARAM_INT);
        $statement->bindValue(':slug', $picture->getSlug());
        $statement->bindValue(':id', $picture->getId());
        $statement->execute();
    }
    
    /**
     * @throws Exception
     */
    public function findBy(array $array): array
    {
        $query = "SELECT * FROM picture WHERE ";
        $parameters = [];
        foreach ($array as $key => $value) {
            $query .= "$key = :$key AND ";
            $parameters[":$key"] = $value;
        }
        $query = rtrim($query, "AND ");
        $statement = $this->database->prepare($query);
        $statement->execute($parameters);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $pictures = [];
        foreach ($rows as $row) {
            $pictures[] = Picture::initializeFromRow($row);
        }
        return $pictures;
    }
    
    /**
     * @throws Exception
     */
    public function findBySlug(string $slug): ?Picture
    {
        
        $query = "SELECT * FROM picture WHERE slug = :slug";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':slug', $slug);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return Picture::initializeFromRow($row);
        } else {
            return null;
        }
    }
}