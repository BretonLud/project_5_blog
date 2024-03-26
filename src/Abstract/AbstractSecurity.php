<?php

namespace App\Abstract;

use App\Entity\User;
use App\Http\RedirectResponse;

abstract class AbstractSecurity
{
    /**
     * @param User|null $user
     * @return true|RedirectResponse
     */
    public function isGrantedAdmin(?User $user): true|RedirectResponse
    {
        if (!$user) {
            return new RedirectResponse('/login');
        }
        
        if ($user->getRole() != 'ADMIN') {
            return new RedirectResponse('/');
        }
        
        return true;
    }
}