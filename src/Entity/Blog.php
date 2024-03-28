<?php

namespace App\Entity;

use DateTime;
use Exception;

class Blog
{
    private int $id;
    private User $user;
    private string $title;
    private \DateTime $created_at;
    private \DateTime $updated_at;
    private string $content;
    private string $slug;
    private array $pictures;
    private string $summary;
    private array $comments;
    
    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->created_at = new DateTime('now');
        $this->updated_at = new DateTime('now');
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
    
    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
    
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }
    
    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt(\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }
    
    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }
    
    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt(\DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
    
    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
    
    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    
    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    /**
     * @param string $slug
     * @return void
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
    
    public function getPictures(): array
    {
        return $this->pictures;
    }
    
    public function setPictures(array $pictures): void
    {
        $this->pictures = $pictures;
    }
    
    public function getSummary(): string
    {
        return $this->summary;
    }
    
    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }
    
    /**
     * @throws Exception
     */
    public static function initializeFromRow(array $row, User $user): self
    {
        $blog = new self();
        $blog->setUser($user);
        $blog->setId($row['id']);
        $blog->setTitle($row['title']);
        $blog->setSlug($row['slug']);
        $blog->setContent($row['content']);
        $blog->setCreatedAt(new \DateTime($row['created_at']));
        $blog->setUpdatedAt(new \DateTime($row['updated_at']));
        $blog->setSummary($row['summary']);
        
        return $blog;
    }
    
    public function getComments(): array
    {
        return $this->comments;
    }
    
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }
}