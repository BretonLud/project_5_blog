<?php

namespace App\Controller\Admin;

use App\ClassAbstract\AbstractController;
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
    public function index(): Response
    {
        $comments = $this->commentRepository->findAll();
        $html = $this->twig->render('admin/comment/index.html.twig', ['comments' => $comments]);
        
        return new Response($html);
    }
    
    /**
     * @throws Exception
     */
    public function validated(int $id): RedirectResponse
    {
        $comment = $this->commentRepository->findOneBy(['id' => $id]);
        $comment->setValidated(true);
        $this->commentRepository->update($comment);
        $_SESSION['success'][] = 'Commentaire validé.';
        return new RedirectResponse('/admin/comment');
    }
    
    /**
     * @throws Exception
     */
    public function delete(int $id): RedirectResponse
    {
        $comment = $this->commentRepository->findOneBy(['id' => $id]);
        $this->commentRepository->delete($comment);
        $_SESSION['success'][] = 'Commentaire supprimé.';
        return new RedirectResponse('/admin/comment');
    }
}