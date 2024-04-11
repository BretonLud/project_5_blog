<?php

namespace App\ClassAbstract;

use App\Database\Connection;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

abstract class AbstractController extends AbstractSecurity
{
    private FilesystemLoader $loader;
    protected Environment $twig;
    
    /**
     * @throws RuntimeError
     */
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
     * @throws RuntimeError
     * @throws \Exception
     */
    private function setupTwig(): void
    {
        $this->loader = new FilesystemLoader(ROOT . '/templates');
        $this->twig = new Environment($this->loader, array(
            'debug' => true
        ));
        $this->twig->addExtension(new DebugExtension());
        $this->twig->getExtension(CoreExtension::class)->setTimezone('Europe/Paris');
    }
    
    /**
     * @return void
     * @throws \Exception
     */
    private function setupTwigGlobals(): void
    {
        $sessionVariables = [
            'error', 'errors', 'success', 'lastname', 'firstname', 'email'
        ];
        
        foreach ($sessionVariables as $var) {
            if (isset($_SESSION[$var])) {
                $this->twig->addGlobal($var, $_SESSION[$var]);
                unset($_SESSION[$var]);
            }
        }
        
        $user = $this->getUser();
        
        $app = ['user' => $user ?: null];
        
        if ($user and $user->getRole() === "ADMIN") {
            $app['unapprovedCommentCount'] = $this->getUnapprovedCommentCount();
        }
        
        $this->twig->addGlobal('app', $app);
    }
    
    /**
     * @throws \Exception
     */
    public function getUnapprovedCommentCount(): int
    {
        $commentRepository = new CommentRepository();
        $unapprovedComments = $commentRepository->findBy(['validated' => false]);
        
        return count($unapprovedComments);
    }
}