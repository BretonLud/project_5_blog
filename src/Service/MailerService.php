<?php

namespace App\Service;

use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailerService
{
    private MailerInterface $mailer;
    
    private string $dsn;
    
    public function __construct(private readonly string $template, private readonly array $options = [])
    {
        $this->dsn = $_ENV['MAILER_DSN'];
        $transport = Transport::fromDsn($this->dsn);
        $this->mailer = new Mailer($transport);
    }
    
    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function sendEmail(string $email, Environment $twig, string $subject, string $replyEmail = ""): void
    {
        $email = (new Email())
            ->from('no-reply@ludovic-breton.fr')
            ->to($email)
            ->subject($subject)
            ->html($twig->render($this->template, $this->options));
        
        if (!empty($replyEmail) && is_string($replyEmail) && preg_match('/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/', $replyEmail)) {
            $email->replyTo($replyEmail);
        }
        
        if (!empty($replyEmail) && is_string($replyEmail) && !preg_match('/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/', $replyEmail)) {
            throw new Exception('Email non valide.');
        }
        
        $this->mailer->send($email);
    }
}