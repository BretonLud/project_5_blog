<?php

namespace App\Controller;


use App\Entity\User;
use App\Http\RedirectResponse;
use App\Http\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController extends Controller
{
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $html = $this->twig->render('home/index.html.twig', [
            'user' => $user,
        ]);
        
        return new Response($html);
    }
}