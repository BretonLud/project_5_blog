<?php

namespace App\Controller\User;

use App\Abstract\AbstractController;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\CommentRepository;
use Exception;

class CommentController extends AbstractController
{
    private CommentRepository $commentRepository;
    
    public function __construct()
    {
        parent::__construct();
        $this->commentRepository = new CommentRepository();
    }
    
    /**
     * @throws Exception
     */
    public function index(): Response|RedirectResponse
    {
        if (!$this->getUser()) {
            return new RedirectResponse('/login');
        }
        
        $comments = $this->commentRepository->findBy(['user_id' => $this->getUser()->getId()]);
        
        $html = $this->twig->render('user/comment/index.html.twig', [
            'comments' => $comments
        ]);
        
        return new Response($html);
    }
}