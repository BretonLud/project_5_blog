<?php

namespace App\Controller\Admin;

use App\Abstract\AbstractController;
use App\Database\Connection;
use App\Entity\User;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\UserRepository;
use App\Service\SlugService;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): Response
    {
        $this->isGrantedAdmin($this->getUser());
        
        $users = (new UserRepository(new Connection()))->findAll();
        
        $html = $this->twig->render('admin/user/index.html.twig', [
            'users' => $users
        ]);
        
        return new Response($html);
    }
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function edit(string $slug): Response|RedirectResponse
    {
        $this->isGrantedAdmin($this->getUser());
        
        $user = (new UserRepository(new Connection()))->findBySlug($slug);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->checkAndUpdateUser($_POST, $slug);
        }
        
        $html = $this->twig->render('admin/user/edit.html.twig', [
            'user' => $user
        ]);
        
        return new Response($html);
    }
    
    private function checkAndUpdateUser(array $post, string $slug): RedirectResponse
    {
        $email = $post['email'];
        $userRepository = new UserRepository(new Connection());
        
        $validator = $this->validatorEditUser($userRepository, $email, $slug);
        
        try {
            $validator->assert($post);
            
            $user = $this->initializeUser($post, $userRepository, $slug);
            
            $userRepository->updateRole($user);
            
            $_SESSION['success'][] = 'Utilisateur modifié';
            
            return new RedirectResponse('/admin/user');
            
        } catch (NestedValidationException $exception) {
            
            $errors = $exception->getMessages();
            
            $_SESSION['error'] = $errors;
            $_SESSION['email'] = $email;
            
            return new RedirectResponse('/admin/user/edit/' . $slug);
        }
        
    }
    
    private function validatorEditUser(UserRepository $userRepository, string $email, string $slug): Validatable
    {
        return v::key('firstname', v::notBlank()
            ->stringType()
            ->regex('/^[a-z \-\p{L}]+$/iu')
            ->setTemplate('Le prénom est nécessaire et doit être composé de lettres'))
            ->key('lastname', v::notBlank()
                ->stringType()
                ->regex('/^[a-z \-\p{L}]+$/iu')
                ->setTemplate('Le nom est nécessaire et doit être composé de lettres'))
            ->key('role', v::notBlank()
                ->stringType()
                ->regex('/^[A-Z]+$/')
                ->setTemplate('Le role est nécessaire et doit être composé de lettres')
            )
            ->key('email', v::notBlank()
                ->email()
                ->regex('/^([[a-zA-Z0-9]+[_\.])*([a-zA-Z0-9\-]+)+@([[a-zA-Z0-9]+[.-])*([a-zA-Z0-9]+\.)+[a-z]{2,}$/')
                ->setTemplate('Merci de mettre un email valide')
            )
            ->key('email')->callback(function () use ($userRepository, $email, $slug) {
                $userExists = $userRepository->findByEmail($email);
                $user = $userRepository->findBySlug($slug);
                
                if (!$userExists || $userExists->getId() === $user->getId()) {
                    return true;
                } else {
                    return false;
                }
                
            })->setTemplate("L'email existe déjà");
    }
    
    private function initializeUser(array $post, UserRepository $userRepository, string $slug): User
    {
        $email = $post['email'];
        $firstName = $post['firstname'];
        $lastName = $post['lastname'];
        $role = $post['role'];
        
        $user = $userRepository->findBySlug($slug);
        
        $user->setEmail($email);
        $user->setFirstname($firstName);
        $user->setLastname($lastName);
        (new SlugService($firstName . ' ' . $lastName, $userRepository, $user))->updateSlug();
        $user->setRole($role);
        
        return $user;
    }
    
    public function delete($slug): RedirectResponse
    {
        $this->isGrantedAdmin($this->getUser());
        
        $userRepository = new UserRepository(new Connection());
        
        $user = $userRepository->findBySlug($slug);
        $userRepository->delete($user);
        
        $_SESSION['success'][] = 'Utilisateur supprimé';
        
        return new RedirectResponse('/admin/user');
    }
}