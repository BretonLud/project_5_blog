<?php

namespace App\Controller;


use App\ClassAbstract\AbstractController;
use App\Entity\Blog;
use App\Http\Response;
use App\Repository\BlogRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController extends AbstractController
{
    private BlogRepository $blogRepository;
    
    public function __construct()
    {
        parent::__construct();
        $this->blogRepository = new BlogRepository();
    }
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws \Exception
     */
    public function index(): Response
    {
        $countBlogs = count($this->blogRepository->findAll());
        $blogs = $this->blogRepository->findBy([''], 'created_at DESC',3);
        $html = $this->twig->render('home/index.html.twig', [
            "blogs" => $blogs,
            "countBlogs" => $countBlogs,
        ]);
        
        return new Response($html);
    }
}