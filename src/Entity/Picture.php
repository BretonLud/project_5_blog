<?php

namespace App\Entity;

use DateTime;

class Picture
{
    private int $id;
    private Blog $blog;
    private DateTime $created_at;
    private string $name;
    private bool $header;
    private string $slug;
    
    public function __construct()
    {
        $this->created_at = new DateTime('now');
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
     * @return Blog|null
     */
    public function getBlog(): ?Blog
    {
        return $this->blog;
    }
    
    /**
     * @param Blog $blog
     * @return void
     */
    public function setBlog(Blog $blog): void
    {
        $this->blog = $blog;
    }
    
    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }
    
    /**
     * @param DateTime $created_at
     * @return void
     */
    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    /**
     * @return bool
     */
    public function getHeader(): bool
    {
        return $this->header;
    }
    
    /**
     * @param bool $header
     * @return void
     */
    public function setHeader(bool $header): void
    {
        $this->header = $header;
    }
    
    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
    
    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}