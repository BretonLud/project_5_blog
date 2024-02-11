<?php

namespace App\Entity;

class User
{
    private int $id;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $password;
    private string $role;
    private bool $validated = false;
    private string $slug;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    
    /**
     * @param string $firstname
     * @return void
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }
    
    
    public function getLastname(): ?string
    {
        return $this->lastname;
    }
    
    /**
     * @param string $lastname
     * @return void
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }
    
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    /**
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    
    public function getRole(): ?string
    {
        return $this->role;
    }
    
    /**
     * @param string $role
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
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
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
    
    
    public function getSlug(): ?string
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