<?php

namespace App\Controller\User;

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
    public function index(): Response|RedirectResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new RedirectResponse('/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->checkAndUpdateUserProfile($_POST);
        }
        
        $html = $this->twig->render('user/index.html.twig', [
            'user' => $user
        ]);
        
        return new Response($html);
    }
    
    /**
     * @param $post
     * @return RedirectResponse
     */
    private function checkAndUpdateUserProfile($post): RedirectResponse
    {
        $email = $post['email'];
        $userRepository = new UserRepository(new Connection());
        
        $validator = $this->validatorEditProfil($userRepository, $email);
        
        try {
            $validator->assert($_POST);
            
            $user = $this->initializeUser($post, $userRepository);
            $userRepository->updateProfil($user);
            
            $_SESSION['success'][] = 'Profil modifié';
            $_SESSION['user']['slug'] = $user->getSlug();
            
        } catch (NestedValidationException $exception) {
            
            $errors = $exception->getMessages();
            
            $_SESSION['error'] = $errors;
            
        }
        
        return new RedirectResponse('/profil');
    }
    
    private function validatorEditProfil(UserRepository $userRepository, string $email): Validatable
    {
        return v::key('firstname', v::notBlank()
            ->stringType()
            ->regex('/^[a-z \-\p{L}]+$/iu')
            ->setTemplate('Le prénom est nécessaire et doit être composé de lettres'))
            ->key('lastname', v::notBlank()
                ->stringType()
                ->regex('/^[a-z \-\p{L}]+$/iu')
                ->setTemplate('Le nom est nécessaire et doit être composé de lettres'))
            ->key('email', v::notBlank()
                ->email()
                ->regex('/^([[a-zA-Z0-9]+[_\.])*([a-zA-Z0-9\-]+)+@([[a-zA-Z0-9]+[.-])*([a-zA-Z0-9]+\.)+[a-z]{2,}$/')
                ->setTemplate('Merci de mettre un email valide')
            )
            ->key('email')->callback(function () use ($userRepository, $email) {
                $userExists = $userRepository->findByEmail($email);
                if (!$userExists || $userExists->getId() === $this->getUser()->getId()) {
                    return true;
                } else {
                    return false;
                }
                
            })->setTemplate("L'email existe déjà");
    }
    
    private function initializeUser($post, UserRepository $userRepository): User
    {
        $email = $post['email'];
        $firstName = $post['firstname'];
        $lastName = $post['lastname'];
        
        $user = $this->getUser();
        
        $user->setEmail($email);
        $user->setFirstname($firstName);
        $user->setLastname($lastName);
        (new SlugService($firstName . ' ' . $lastName, $userRepository, $user))->updateSlug();
        
        return $user;
    }
    
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function editPassword(): Response|RedirectResponse
    {
        if (!$this->getUser()) {
            return new RedirectResponse('/login');
        }
        
        $html = $this->twig->render('user/edit_password.html.twig', [
            'edit' => true
        ]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userRepository = new UserRepository(new Connection());
            
            $validator = $this->validatorEditPassword($_POST, $userRepository);
            
            try {
                $validator->assert($_POST);
                
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $userRepository->updatePassword($this->getUser(), $password);
                
                $_SESSION['success'][] = 'Mot de passe modifié';
                
                return new RedirectResponse('/profil');
                
            } catch (NestedValidationException $exception) {
                
                $errors = $exception->getMessages();
                
                $_SESSION['error'] = $errors;
                
                return new RedirectResponse('/profil/edit-password', [
                    'edit' => true
                ]);
            }
        }
        
        return new Response($html);
    }
    
    private function validatorEditPassword(array $post, UserRepository $userRepository): Validatable
    {
        $currentPassword = $post['currentPassword'];
        $passwordValidator = v::notBlank()->length(8, 64)->regex('((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,64})')
            ->setTemplate(
                'Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, et un caractère spécial.'
            );
        
        
        return v::key('password', $passwordValidator)
            ->key('confirm_password', v::equals($_POST['password'])->setTemplate('Les mots de passe doivent être identiques'))
            ->key('currentPassword')->callback(function () use ($currentPassword, $userRepository) {
                $user = $userRepository->findByEmail($this->getUser()->getEmail());
                return password_verify($currentPassword, $user->getPassword());
            })->setTemplate('Le mot de passe actuel n\'est pas correct');
    }
}