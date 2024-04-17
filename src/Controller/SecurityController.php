<?php

namespace App\Controller;

use App\ClassAbstract\AbstractController;
use App\Database\Connection;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SecurityController extends AbstractController
{
    
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function login(): Response|RedirectResponse
    {
        if ($this->getUser()) {
            return new RedirectResponse('/');
        }
        
        $html = $this->twig->render('security/login.html.twig');
        
        return new Response($html);
    }
    
    /**
     * @return RedirectResponse
     */
    public function handleLoginSubmit(): RedirectResponse
    {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new RedirectResponse('/login');
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $authService = new AuthService(new UserRepository(new Connection()));
        
        try {
            
            return $authService->authenticate($email, $password);
            
        } catch (\Exception $exception) {
            
            $_SESSION['error']['login'] = $exception->getMessage();
            
            return new RedirectResponse('/login');
        }
    }
    
    /**
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        if ($this->getUser()) {
            unset($_SESSION['user']);
            
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
        }
        
        return new RedirectResponse('/');
    }
}