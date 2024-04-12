<?php

namespace App\Controller;

use App\ClassAbstract\AbstractController;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use Exception;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;

class CommentController extends AbstractController
{
    private CommentRepository $commentRepository;
    private BlogRepository $blogRepository;
    
    public function __construct()
    {
        parent::__construct();
        $this->commentRepository = new CommentRepository();
        $this->blogRepository = new BlogRepository();
    }
    
    /**
     * @throws Exception
     */
    public function edit(string $slug, int $id): Response|RedirectResponse
    {
        try {
            $comment = $this->commentRepository->findOneBy(['id' => $id]);
        } catch (Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return new RedirectResponse("/blogs/show/$slug");
        }
        
        if ($comment->getUser() != $this->getUser() and $this->getUser()->getRole() != "ADMIN") {
            $_SESSION['errors'][] = "Impossible de modifier un commentaire qui n'est pas le votre.";
            return new RedirectResponse("/blogs/show/$slug");
        }
        
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $validator = $this->validator();
            
            try {
                $validator->assert($_POST);
                $commentContent = $_POST['commentContent'];
                $comment->setContent($commentContent);
                $comment->setUpdatedAt(new \DateTime('now'));
                $this->commentRepository->update($comment);
                return new RedirectResponse("/blogs/show/$slug");
            } catch (NestedValidationException $exception) {
                $validationErrors = $exception->getMessages();
                $_SESSION['error'] = $validationErrors;
                return new RedirectResponse("/blogs/show/$slug/comment/edit/$id");
            }
            
        }
        
        $html = $this->twig->render('blog/comment/edit.html.twig', [
            'comment' => $comment
        ]);
        
        return new Response($html);
    }
    
    /**
     * @throws Exception
     */
    public function delete(string $slug, int $id): RedirectResponse
    {
        try {
            $comment = $this->commentRepository->findOneBy(['id' => $id]);
        } catch (Exception $exception) {
            $_SESSION['errors'][] = $exception->getMessage();
            return new RedirectResponse("/blogs/show/$slug");
        }
        
        if ($comment->getUser() != $this->getUser() and $this->getUser()->getRole() != "ADMIN") {
            $_SESSION['errors'][] = "Impossible de supprimer un commentaire qui n'est pas le votre.";
            return new RedirectResponse("/blogs/show/$slug");
        }
        
        $this->commentRepository->delete($comment);
        
        $_SESSION['success'][] = 'Commentaire supprimé.';
        return new RedirectResponse("/blogs/show/$slug");
    }
    
    private function validator(): Validatable
    {
        return v::key('commentContent', v::notBlank()->stringType()->regex('/^[^<>]*$/')->setTemplate('Le texte est requis et ne doit pas être composé des symboles < et >'));
    }
}