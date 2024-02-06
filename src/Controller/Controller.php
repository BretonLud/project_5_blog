<?php

namespace App\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    private FilesystemLoader $loader;
    protected Environment $twig;
    
    public function __construct()
    {
        $this->loader = new FilesystemLoader(ROOT . '/templates');
        $this->twig = new Environment($this->loader);
    }
}