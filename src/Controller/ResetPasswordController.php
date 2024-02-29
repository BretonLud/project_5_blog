<?php

namespace App\Controller;

use App\Database\Connection;
use App\Entity\User;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\TokenService;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ResetPasswordController extends Controller
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function indexResetPassword(): RedirectResponse|Response
    {
        if ($this->getUser()) {
            return new RedirectResponse('/');
        }
        
        $html = $this->twig->render('reset_password/reset_password.html.twig');
        
        return new Response($html);
    }
    
    /**
     * @return RedirectResponse
     */
    public function handleResetPasswordSubmit(): RedirectResponse
    {
        $validator = v::key('email', v::notBlank()
            ->email()
            ->regex('/^([[a-zA-Z0-9]+[_\.])*([a-zA-Z0-9\-]+)+@([[a-zA-Z0-9]+[.-])*([a-zA-Z0-9]+\.)+[a-z]{2,}$/')
            ->setTemplate('Merci de mettre un email valide'));
        
        try {
            $validator->assert($_POST);
            
            
            $user = (new UserRepository(new Connection()))->findByEmail($_POST['email']);
            
            if ($user instanceof User) {
                return $this->sendResetPassword($user);
            }
            
            return new RedirectResponse('/reset-password/confirm-send');
            
            
        } catch (NestedValidationException $exception) {
            $errors = $exception->getMessages();
            $_SESSION['error'] = $errors;
            
            return new RedirectResponse('/reset-password');
        }
    }
    
    /**
     * @param User $user
     * @return RedirectResponse
     */
    private function sendResetPassword(User $user): RedirectResponse
    {
        
        // Generate token
        $tokenService = new TokenService();
        $tokenService->setExpire(600);
        $token = $tokenService->generateToken($user->getId(), $user->getEmail());
        
        // Create verification link
        $verificationLink = "http://localhost/reset-password/verify-token/?token=" . $token;
        
        // Send email
        $mailerService = new MailerService('localhost', 1025, 'email/reset-password.html.twig', [
            'link' => $verificationLink
        ]);
        
        try {
            $mailerService->sendEmail($user->getEmail(), $this->twig, 'Reset password');
            $_SESSION['success'][] = 'Email envoyé';
            
            return new RedirectResponse('/reset-password/confirm-send');
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
            
            $_SESSION['errors'][] = $error;
            
            return new RedirectResponse('/reset-password');
        }
        
    }
    
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function confirmSend(): Response|RedirectResponse
    {
        if ($this->getUser()) {
            return new RedirectResponse('/');
        }
        
        $html = $this->twig->render('reset_password/check_email.html.twig');
        
        return new Response($html);
    }
    
    /**
     * @return Response|RedirectResponse
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function verifyResetPasswordToken(): RedirectResponse
    {
        $token = $_GET['token'];
        
        $tokenService = new TokenService();
        try {
            $decodedToken = $tokenService->decodeToken($token);
            if (isset($_SESSION['token'])) {
                unset($_SESSION['token']);
            }
            
            $_SESSION['token'] = $token;
            
        } catch (\Exception $exception) {
            
            $_SESSION['errors'][] = $exception->getMessage();
            return new RedirectResponse('/reset-password/index');
        }
        
        $user = (new UserRepository(new Connection()))->findByEmail($decodedToken->email);
        
        if ($user instanceof User) {
            return new RedirectResponse('/reset-password');
        }
        
        $_SESSION['errors'][] = "Le lien n'est pas valide.";
        return new RedirectResponse('/reset-password/index');
    }
    
    
    /**
     * @return RedirectResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function resetPassword(): Response|RedirectResponse
    {
        if (!isset($_SESSION['token'])) {
            return new RedirectResponse('/login');
        }
        
        $html = $this->twig->render('user/edit_password.html.twig');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $passwordValidator = v::notBlank()->length(8, 64)->regex('((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,64})')
                ->setTemplate(
                    'Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, et un caractère spécial.'
                );
            
            $validator = v::key('password', $passwordValidator)
                ->key('confirm_password', v::equals($_POST['password'])->setTemplate('Les mots de passe doivent être identiques'));
            
            try {
                $validator->assert($_POST);
                
                return $this->updatePassword($_POST);
                
            } catch (NestedValidationException $exception) {
                
                $errors = $exception->getMessages();
                
                $_SESSION['error'] = $errors;
                
                return new RedirectResponse('/reset-password');
            }
        }
        
        return new Response($html);
    }
    
    /**
     * @param $post
     * @return RedirectResponse
     */
    private function updatePassword($post): RedirectResponse
    {
        $password = $post['password'];
        
        $token = $_SESSION['token'];
        $tokenService = new TokenService();
        $decodedToken = $tokenService->decodeToken($token);
        
        $userRepository = new UserRepository(new Connection());
        
        
        $user = $userRepository->findByEmail($decodedToken->email);
        
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        
        $userRepository->updateUser($user);
        
        $_SESSION['success'][] = 'Mot de passe réinitialisé avec succès';
        
        unset($_SESSION['token']);
        
        return new RedirectResponse('/login');
        
    }
}