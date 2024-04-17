<?php

namespace App\Controller;

use App\ClassAbstract\AbstractController;
use App\Http\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ErrorController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderError(int $errorCode, string $errorMessage): Response
    {
        $html = $this->twig->render('error/error.html.twig', [
            'errorCode' => $errorCode,
            'errorMessage' => $errorMessage
        ]);
        
        return new Response($html);
    }
}