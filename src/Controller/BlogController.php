<?php

namespace App\Controller;

use App\Abstract\AbstractController;
use App\Database\Connection;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Entity\Comment;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use Exception;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class BlogController extends AbstractController
{
    private BlogRepository $blogRepository;
    private CommentRepository $commentRepository;
    
    
    public function __construct()
    {
        parent::__construct();
        $connection = new Connection();
        $this->blogRepository = new BlogRepository();
        $this->commentRepository = new CommentRepository($connection);
    }
    
    /**
     * @throws Exception
     */
    public function index(): Response
    {
        $blogs = $this->blogRepository->findAll();
        
        $html = $this->twig->render('blog/index.html.twig', ['blogs' => $blogs]);
        
        return new Response($html);
    }
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function show(string $slug): Response
    {
        try {
            $blog = $this->blogRepository->findBySlug($slug);
        } catch (Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return new RedirectResponse('/blogs');
        }
        
        $blog->setComments($this->commentRepository->findBy(['blog_id' => $blog->getId(), 'validated' => true], 'created_at DESC'));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (!$this->getUser()) {
                new RedirectResponse("/blogs/show/$slug", ['blog' => $blog]);
            }
            
            $validator = $this->validator();
            
            try {
                $validator->assert($_POST);
                $comment = new Comment();
                $comment->setContent($_POST['commentContent']);
                $comment->setUser($this->getUser());
                $comment->setBlog($blog);
                
                try {
                    $this->commentRepository->create($comment);
                    $_SESSION['success'][] = 'Votre commentaire a bien été ajouté et soumis à validation';
                } catch (Exception $exception) {
                    $_SESSION['errors'][] = $exception->getMessage();
                }
                
            } catch (NestedValidationException $exception) {
                $validationErrors = $exception->getMessages();
                $_SESSION['error'] = $validationErrors;
            }
            
            return new RedirectResponse("/blogs/show/$slug", ['blog' => $blog]);
            
        }
        
        $html = $this->twig->render('blog/show.html.twig', ['blog' => $blog]);
        
        return new Response($html);
    }
    
    private function validator(): Validatable
    {
        return v::key('commentContent', v::notBlank()->stringType()->regex('/^[^<>]*$/')->setTemplate('Le texte est requis et ne doit pas être composé des symboles < et >'));
    }
}