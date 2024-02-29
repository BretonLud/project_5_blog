<?php

namespace App\Entity;

class Comment
{
    private int $id;
    private User $user;
    private \DateTime $created_at;
    private \DateTime $updated_at;
    private string $content;
    private bool $validated = false;
    
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
     * @return void
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
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
     * @return void
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
     * @return void
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
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    
    /**
     * @return bool
     */
    public function getValidated(): bool
    {
        return $this->validated;
    }
    
    /**
     * @param bool $validated
     * @return void
     */
    public function setValidated(bool $validated): void
    {
        $this->validated = $validated;
    }
}