<?php

namespace App\Controller;


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
        $html = $this->twig->render('home/index.html.twig');
        
        return new Response($html);
    }
}