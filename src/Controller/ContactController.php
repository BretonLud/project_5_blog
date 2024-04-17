<?php

namespace App\Controller;

use App\ClassAbstract\AbstractController;
use App\Http\Response;
use App\Service\MailerService;
use Exception;
use Respect\Validation\Exceptions\NestedValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;

class ContactController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     */
    public function index(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = $this->validator();
            
            try {
                $validator->assert($_POST);
                return new Response($this->send($_POST));
            } catch (NestedValidationException $exception) {
                $errors['error'] = $exception->getMessages();
                return new Response(json_encode($errors));
            }
            
        }
        
        $html = $this->twig->render('contact/index.html.twig');
        
        return new Response($html);
    }
    
    /**
     * @param array $data
     * @return string
     * @throws TransportExceptionInterface
     */
    private function send(array $data): string
    {
        $message = array_map(fn($data) => trim(htmlspecialchars($data)), $data);
        
        $mailerService = new MailerService('email/contact.html.twig', [
            'message' => $message
        ]);
        
        try {
            
            $mailerService->sendEmail('contact@ludovic-breton.fr', $this->twig, 'Prise de contact', $message['email']);
            $message = [
                'success' => 'Email envoyé'
            ];
            return json_encode($message);
        } catch (Exception $exception) {
            return json_encode(['error' => $exception->getMessage()]);
        }
    }
    
    private function validator(): Validatable
    {
        return v::key('fullname', v::stringType()->notBlank()->length(1, 255)->setTemplate('Le nom complet est requis et ne doit pas dépasser 255 caractères.'))
            ->key('email', v::email()->notBlank()->regex('/^([[a-zA-Z0-9]+[_\.])*([a-zA-Z0-9\-]+)+@([[a-zA-Z0-9]+[.-])*([a-zA-Z0-9]+\.)+[a-z]{2,}$/')->setTemplate('L\'email n\'est pas valide.'))
            ->key('phone', v::optional(v::phone())->setTemplate('Le numéro de téléphone n\'est pas valide.'))
            ->key('subject', v::stringType()->notBlank()->length(1, 255)->setTemplate('Le sujet est requis et ne doit pas dépasser 255 caractères.'))
            ->key('message', v::stringType()->notBlank()->setTemplate('Le message ne peut pas être vide.'));
    }
}