<?php

namespace App\Controller;

use App\Abstract\AbstractController;
use App\Database\Connection;
use App\Entity\User;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\SlugService;
use App\Service\TokenService;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegisterController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function register(): Response|RedirectResponse
    {
        if ($this->getUser()) {
            return new RedirectResponse('/');
        }
        
        $html = $this->twig->render('register/register.html.twig');
        
        return new Response($html);
    }
    
    /**
     * @return Response|RedirectResponse
     */
    public function handleRegisterSubmit(): Response|RedirectResponse
    {
        
        $userRepository = new UserRepository(new Connection());
        
        $email = $_POST['email'];
        
        $validator = $this->validator($userRepository, $email);
        
        try {
            $validator->assert($_POST);
            
            if ($this->create($_POST, $userRepository)) {
                return new RedirectResponse('/login');
            }
            
            return new RedirectResponse('/register');
            
        } catch (NestedValidationException $exception) {
            
            $errors = $exception->getMessages();
            
            $_SESSION['error'] = $errors;
            $_SESSION['lastname'] = $_POST['lastname'];
            $_SESSION['firstname'] = $_POST['firstname'];
            
            return new RedirectResponse('/register');
        }
    }
    
    private function validator(UserRepository $userRepository, string $email): Validatable
    {
        $passwordValidator = v::notBlank()->length(8, 64)->regex('((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,64})')
            ->setTemplate(
                'Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, et un caractère spécial.'
            );
        
        $validator = v::key('firstname', v::notBlank()
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
            ->key('password', $passwordValidator)
            ->key('confirm_password', v::equals($_POST['password'])->setTemplate('Les mots de passe doivent être identiques'))
            ->key('email')->callback(function () use ($userRepository, $email) {
                $userExists = $userRepository->findByEmail($email);
                
                return $userExists === null;
            })->setTemplate("L'email existe déjà");
        
        return $validator;
    }
    
    /**
     * @param $POST
     * @param UserRepository $userRepository
     * @return bool
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function create($POST, UserRepository $userRepository): bool
    {
        $firstName = $POST['firstname'];
        $lastName = $POST['lastname'];
        $email = $POST['email'];
        $password = $POST['password'];
        
        
        $user = new User();
        $user->setRole('USER');
        $user->setEmail($email);
        $user->setLastname($lastName);
        $user->setFirstname($firstName);
        (new SlugService($firstName . ' ' . $lastName, $userRepository, $user))->updateSlug();
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        
        try {
            $userRepository->addUser($user);
            
        } catch (\Exception $exception) {
            $_SESSION['errors'] = $exception->getMessage();
            return false;
        }
        
        $user = $userRepository->findBySlug($user->getSlug());
        
        $this->sendVerifyEmail($user);
        
        $_SESSION['success'][] = 'Votre compte a bien été créé';
        
        return true;
    }
    
    /**
     * @param string $slug
     * @return RedirectResponse
     * @throws TransportExceptionInterface
     */
    public function resendVerifyEmail(string $slug): RedirectResponse
    {
        if (!$this->getUser()) {
            return new RedirectResponse('/login');
        }
        
        if ($this->getUser()->getValidated()) {
            return new RedirectResponse('/');
        }
        
        $userRepository = new UserRepository(new Connection());
        $user = $userRepository->findBySlug($slug);
        
        try {
            $this->sendVerifyEmail($user);
        } catch (\Exception $exception) {
            $_SESSION['errors'][] = "Impossible de vous renvoyer l'email actuellement.";
        }
        
        $_SESSION['success'][] = "L'email pour vérifier votre email, vous a été envoyé.";
        
        return new RedirectResponse('/');
    }
    
    /**
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     */
    private function sendVerifyEmail(User $user): void
    {
        
        // Generate token
        $tokenService = new TokenService();
        $token = $tokenService->generateToken($user->getId(), $user->getEmail());
        
        // Create verification link
        $verificationLink = "http://localhost/verify-email?token=" . $token;
        
        // Send email
        $mailerService = new MailerService('email/verify.html.twig', [
            'link' => $verificationLink
        ]);
        
        $mailerService->sendEmail($user->getEmail(), $this->twig, 'Verify Email');
    }
    
    /**
     * @return RedirectResponse
     */
    public function verifyEmail(): RedirectResponse
    {
        
        $token = $_GET['token'];
        
        $tokenService = new TokenService();
        try {
            $decodedToken = $tokenService->decodeToken($token);
        } catch (\Exception $exception) {
            
            $_SESSION['errors'][] = $exception->getMessage();
            
            if ($this->getUser()) {
                return new RedirectResponse('/');
            }
            
            return new RedirectResponse('/login');
        }
        
        $userRepository = new UserRepository(new Connection());
        
        $this->validateUserEmail($decodedToken, $userRepository);
        
        return new RedirectResponse('/login');
    }
    
    /**
     * @param $decodedToken
     * @param UserRepository $userRepository
     * @return void
     */
    private function validateUserEmail($decodedToken, UserRepository $userRepository): void
    {
        $userId = $decodedToken->userId;
        $email = $decodedToken->email;
        $user = $userRepository->findByEmail($email);
        
        if ($user instanceof User && $user->getId() == $userId) {
            $user->setValidated(true);
            $userRepository->updateUser($user);
            
            $_SESSION['success'][] = 'Votre email a été vérifié avec succès';
        } else {
            $_SESSION['errors'][] = 'Le lien de vérification est invalide';
        }
    }
}