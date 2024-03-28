<?php

namespace App\Repository;

use App\Database\Connection;
use App\Entity\Blog;
use App\Entity\Picture;
use Exception;

class BlogRepository
{
    private Connection $connection;
    private UserRepository $userRepository;
    private PictureRepository $pictureRepository;
    
    public function __construct()
    {
        $this->connection = new Connection();
        $this->userRepository = new UserRepository();
        $this->pictureRepository = new PictureRepository();
    }
    
    /**
     * @throws Exception
     */
    public function findAll(): array
    {
        $database = $this->connection->getConnection();
        $statement = $database->prepare("SELECT * from blog order by created_at DESC ");
        $statement->execute();
        
        $blogs = [];
        
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $blog = $this->setBlog($row, $database);
            
            $blogs[] = $blog;
        }
        
        return $blogs;
    }
    
    /**
     * @throws Exception
     */
    public function findBySlug($slug): ?Blog
    {
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare("SELECT * from blog WHERE slug = :slug");
        $statement->bindParam(':slug', $slug);
        $statement->execute();
        
        $blog = null;
        if ($row = $statement->fetch()) {
            $blog = $this->setBlog($row, $database);
        }
        
        
        return $blog;
    }
    
    /**
     * @throws Exception
     */
    private function setBlog(mixed $row, $database): Blog
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
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare("INSERT INTO blog (created_at, updated_at, slug, title, content, user_id, summary)
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
            $blog->setId($database->lastInsertId());
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
        
        $database = $this->connection->getConnection();
        $statement = $database->prepare("UPDATE blog SET updated_at = :updated_at, slug = :slug, title = :title, content = :content WHERE id = :id");
        $statement->bindParam(':updated_at', $updatedAt);
        $statement->bindParam(':slug', $slug);
        $statement->bindParam(':title', $title);
        $statement->bindParam(':content', $content);
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
        $database = $this->connection->getConnection();
        $statement = $database->prepare('DELETE FROM blog WHERE id = :id');
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
        $database = $this->connection->getConnection();
        $statement = $database->prepare("SELECT * from blog WHERE id = :id");
        $statement->bindParam(':id', $blog_id);
        $statement->execute();
        
        $blog = null;
        if ($row = $statement->fetch()) {
            $blog = $this->setBlog($row, $database);
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
    
}