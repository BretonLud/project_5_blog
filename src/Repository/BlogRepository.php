<?php

namespace App\Repository;

use App\Database\Connection;
use App\Entity\Blog;
use App\Entity\Picture;
use Exception;
use PDO;

class BlogRepository
{
    private Connection $connection;
    private UserRepository $userRepository;
    private PictureRepository $pictureRepository;
    private PDO $database;
    
    public function __construct()
    {
        $this->connection = new Connection();
        $this->userRepository = new UserRepository();
        $this->pictureRepository = new PictureRepository();
        $this->database = $this->connection->getConnection();
    }
    
    /**
     * @throws Exception
     */
    public function findAll(): array
    {
       
        $statement = $this->database->prepare("SELECT * from blog order by created_at DESC ");
        $statement->execute();
        
        $blogs = [];
        
        while (($row = $statement->fetch(PDO::FETCH_ASSOC))) {
            $blog = $this->setBlog($row,);
            
            $blogs[] = $blog;
        }
        
        return $blogs;
    }
    
    /**
     * @throws Exception
     */
    public function findBySlug(string $slug, string $action = ''): ?Blog
    {
        $statement = $this->database->prepare("SELECT * from blog WHERE slug = :slug");
        $statement->bindParam(':slug', $slug);
        $statement->execute();
        $result = $statement->fetch();
        
        if (!$result and !$action) {
            throw new Exception('Impossible de retrouver le blog');
        }
        
        if (!$result) {
            return null;
        }
        
        return $this->setBlog($result);
    }
    
    /**
     * @throws Exception
     */
    private function setBlog(mixed $row): Blog
    {
        
        $user = $this->userRepository->find($row['user_id']);
        
        // Use new Blog method to initialize blog from a row
        $blog = Blog::initializeFromRow($row, $user);
        
        // Moved fetching pictures into its own method for organization
        $pictures = $this->fetchPicturesForBlog($blog->getId());
        
        $blog->setPictures($pictures);
        
        return $blog;
    }
    
    public function create(Blog $blog): Blog|null
    {
        $createdAt = $blog->getCreatedAt()->format('Y-m-d H:i:s');
        $slug = $blog->getSlug();
        $title = $blog->getTitle();
        $content = $blog->getContent();
        $userId = $blog->getUser()->getId();
        $updatedAt = $blog->getUpdatedAt()->format('Y-m-d H:i:s');
        $summary = $blog->getSummary();
        
       
        $statement = $this->database->prepare("INSERT INTO blog (created_at, updated_at, slug, title, content, user_id, summary)
            VALUES (:created_at, :updated_at, :slug, :title, :content, :user, :summary)");
        $statement->bindParam(':created_at', $createdAt);
        $statement->bindParam(':updated_at', $updatedAt);
        $statement->bindParam(':slug', $slug);
        $statement->bindParam(':title', $title);
        $statement->bindParam(':content', $content);
        $statement->bindParam(':user', $userId);
        $statement->bindParam(':summary', $summary);
        
        try {
            $statement->execute();
            $blog->setId($this->database->lastInsertId());
            return $blog;
        } catch (Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            
            return null;
        }
        
    }
    
    public function update(Blog $blog): bool
    {
        $slug = $blog->getSlug();
        $title = $blog->getTitle();
        $content = $blog->getContent();
        $updatedAt = $blog->getUpdatedAt()->format('Y-m-d H:i:s');
        $id = $blog->getId();
        $summary = $blog->getSummary();
        
        $statement = $this->database->prepare("UPDATE blog SET updated_at = :updated_at, slug = :slug, title = :title, content = :content, summary = :summary WHERE id = :id");
        $statement->bindParam(':updated_at', $updatedAt);
        $statement->bindParam(':slug', $slug);
        $statement->bindParam(':title', $title);
        $statement->bindParam(':content', $content);
        $statement->bindParam(':summary', $summary);
        $statement->bindParam(':id', $id);
        
        try {
            $statement->execute();
        } catch (Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            
            return false;
        }
        
        return true;
    }
    
    public function delete(Blog $blog): bool
    {
        $id = $blog->getId();
        $statement = $this->database->prepare('DELETE FROM blog WHERE id = :id');
        $statement->bindParam(':id', $id);
        
        try {
            $statement->execute();
        } catch (Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return false;
        }
        return true;
    }
    
    /**
     * @throws Exception
     */
    public function findBy(array $array, string $order = "", int $limit = 0): false|array
    {
        
        $stmt = $this->prepareStmt($array, $order, $limit);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $blogs = [];
        foreach ($results as $result) {
            $blog = $this->setBlog($result);
            $blogs[] = $blog;
        }
        
        return $blogs;
    }
    
    
    /**
     * @throws Exception
     */
    private function setPicture(mixed $row): Picture
    {
        $picture = new Picture();
        $picture->setId($row['id']);
        $picture->setCreatedAt(new \DateTime($row['created_at']));
        $picture->setName($row['name']);
        $picture->setHeader($row['header']);
        $picture->setSlug($row['slug']);
        
        return $picture;
    }
    
    /**
     * @throws Exception
     */
    public function find(mixed $blog_id): ?Blog
    {
        $statement = $this->database->prepare("SELECT * from blog WHERE id = :id");
        $statement->bindParam(':id', $blog_id);
        $statement->execute();
        
        $blog = null;
        if ($row = $statement->fetch()) {
            $blog = $this->setBlog($row);
        }
        
        return $blog;
    }
    
    /**
     * @throws Exception
     */
    private function fetchPicturesForBlog(int $blogId): array
    {
        return $this->pictureRepository->findBy(['blog_id' => $blogId]);
    }
    
    private function prepareStmt(array $array, string $order, int $limit): false|\PDOStatement
    {
        $query = "SELECT * FROM blog WHERE ";
        $conditions = [];
        foreach ($array as $key => $value) {
            $conditions[] = "$key = :$key";
        }
        $query .= implode(" AND ", $conditions);
        
        if ($order) {
            $query .= " ORDER BY $order";
        }
        
        if ($limit)
        {
            $query .= " LIMIT $limit";
        }
        
        $stmt = $this->database->prepare($query);
        
        foreach ($array as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt;
    }
}