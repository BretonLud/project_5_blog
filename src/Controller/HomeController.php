<?php

namespace App\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->twig->display('home/index.html.twig');
    }
}