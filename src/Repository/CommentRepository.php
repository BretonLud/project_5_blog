<?php

namespace App\Repository;

use App\Database\Connection;
use App\Entity\Blog;
use App\Entity\Comment;
use Exception;
use PDO;

class CommentRepository
{
    
    private UserRepository $userRepository;
    private BlogRepository $blogRepository;
    private Connection $connection;
    private PDO $database;
    
    public function __construct()
    {
        $this->connection = new Connection();
        $this->userRepository = new UserRepository();
        $this->blogRepository = new BlogRepository();
        $this->database = $this->connection->getConnection();
    }
    
    public function create(Comment $comment): bool
    {
        $query = "INSERT INTO comment (user_id, blog_id, created_at, updated_at, content, validated)
                  VALUES (:user_id, :blog_id, :created_at, :updated_at, :content, :validated)";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':user_id', $comment->getUser()->getId());
        $stmt->bindValue(':blog_id', $comment->getBlog()->getId());
        $stmt->bindValue(':created_at', $comment->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $comment->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':validated', $comment->getValidated(), PDO::PARAM_BOOL);
        
        try {
            $stmt->execute();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
    
    /**
     * @throws Exception
     */
    public function findBy(array $array, string $order = ""): false|array
    {
        
        $stmt = $this->prepareStmt($array, $order);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $comments = [];
        foreach ($results as $result) {
            $comment = $this->setComment($result);
            $comments[] = $comment;
        }
        
        return $comments;
    }
    
    /**
     * @throws Exception
     */
    public function findOneBy(array $array, string $order = ""): false|Comment
    {
        $stmt = $this->prepareStmt($array, $order);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception("Commentaire non trouvé");
        }
        
        return $this->setComment($result);
    }
    
    /**
     * @throws Exception
     */
    public function findAll(): array
    {
        $query = "SELECT * FROM comment ORDER BY created_at DESC ";
        
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        
        $comments = [];
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {
            $comment = $this->setComment($result);
            $comments[] = $comment;
        }
        
        return $comments;
    }
    
    public function update(Comment $comment): bool
    {
        $query = "UPDATE comment SET user_id = :user_id, blog_id = :blog_id, updated_at = :updated_at, content = :content, validated = :validated WHERE id = :id";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':user_id', $comment->getUser()->getId());
        $stmt->bindValue(':blog_id', $comment->getBlog()->getId());
        $stmt->bindValue(':updated_at', $comment->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':validated', $comment->getValidated(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $comment->getId());
        try {
            $stmt->execute();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
    
    public function delete(Comment $comment): void
    {
        $stmt = $this->database->prepare("DELETE FROM comment WHERE id = :id");
        $stmt->bindValue(':id', $comment->getId());
        $stmt->execute();
    }
    
    /**
     * @throws Exception
     */
    public function deleteByBlog(Blog $blog): void
    {
        $comments = $this->findBy(['blog_id' => $blog->getId()]);
        foreach ($comments as $comment) {
            $this->delete($comment);
        }
    }
    
    private function prepareStmt(array $array, string $order): false|\PDOStatement
    {
        $query = "SELECT * FROM comment WHERE ";
        $conditions = [];
        foreach ($array as $key => $value) {
            $conditions[] = "$key = :$key";
        }
        $query .= implode(" AND ", $conditions);
        
        if ($order) {
            $query .= " ORDER BY $order";
        }
        
        $stmt = $this->database->prepare($query);
        
        foreach ($array as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt;
    }
    
    /**
     * @throws Exception
     */
    private function setComment(array $result): Comment
    {
        $user = $this->userRepository->find($result['user_id']);
        $blog = $this->blogRepository->find($result['blog_id']);
        return Comment::initializeFromRow($result, $user, $blog);
    }
    
    
}