<?php

namespace App\ClassAbstract;

use App\Entity\User;
use App\Http\RedirectResponse;

abstract class AbstractSecurity
{
    /**
     * @param User|null $user
     * @return RedirectResponse|bool
     */
    public function isGrantedAdmin(?User $user): RedirectResponse|bool
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