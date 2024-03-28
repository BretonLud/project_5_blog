<?php

namespace App\Controller\Admin;

use App\Abstract\AbstractController;
use App\Http\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AdminController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function adminDashboard(): Response
    {
        $this->isGrantedAdmin($this->getUser());
        
        $html = $this->twig->render('admin/index.html.twig');
        
        return new Response($html);
    }
}