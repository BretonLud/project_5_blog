<?php

namespace App\Service;

use App\Entity\User;
use App\Http\RedirectResponse;
use App\Repository\UserRepository;

class AuthService
{
    
    public function __construct(private UserRepository $repository)
    {
    }
    
    /**
     * Authenticate a user.
     *
     * @param string $username
     * @param string $password
     *
     * @return User|null The authenticated user, or null if authentication failed.
     */
    public function authenticate(string $email, string $password): RedirectResponse
    {
        // Retrieve the user by username. This will depend on your User model.
        $user = $this->repository->findByEmail($email);
        
        // If no user was found, or the password doesn't match, return null.
        if (!$user || !password_verify($password, $user->getPassword())) {
            return $this->authenticateFailure($email);
        }
        
        // The password matches. Return the user.
        return $this->authenticateSuccess($user);
    }
    
    private function authenticateFailure(string $email): RedirectResponse
    {
        $_SESSION['error']['email'] = $email;
        $_SESSION['error']['login'] = 'Email ou mot de passe incorrect.';
        return new RedirectResponse('./login');
    }
    
    private function authenticateSuccess(User $user): RedirectResponse
    {
        $_SESSION['user'] = [
            'slug' => $user->getSlug(),
        ];
        
        return new RedirectResponse('./');
    }
}