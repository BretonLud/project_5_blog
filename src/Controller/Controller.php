<?php

namespace App\Controller;

use App\Database\Connection;
use App\Entity\User;
use App\Repository\UserRepository;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    private FilesystemLoader $loader;
    protected Environment $twig;
    
    public function __construct()
    {
        $this->loader = new FilesystemLoader(ROOT . '/templates');
        $this->twig = new Environment($this->loader, array(
            'debug' => true
        ));
        
        $this->twig->addExtension(new DebugExtension());
        $this->setupTwig();
        $this->startSession();
        $this->setupTwigGlobals();
    }
    
    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        $userSlug = $_SESSION['user']['slug'] ?? null;
        
        if (!$userSlug) {
            return null;
        }
        
        $user = (new UserRepository(new Connection()))->findBySlug($_SESSION['user']['slug']);
        
        if ($user && !$user->getValidated()) {
            $this->twig->addGlobal('validated', true);
        }
        
        return $user;
    }
    
    /**
     * @return void
     */
    private function startSession(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * @return void
     */
    private function setupTwig(): void
    {
        $this->loader = new FilesystemLoader(ROOT . '/templates');
        $this->twig = new Environment($this->loader, array(
            'debug' => true
        ));
        $this->twig->addExtension(new DebugExtension());
    }
    
    /**
     * @return void
     */
    private function setupTwigGlobals(): void
    {
        $sessionVariables = [
            'error', 'errors', 'success', 'lastname', 'firstname'
        ];
        
        foreach ($sessionVariables as $var) {
            if (isset($_SESSION[$var])) {
                $this->twig->addGlobal($var, $_SESSION[$var]);
                unset($_SESSION[$var]);
            }
        }
    }
    
}