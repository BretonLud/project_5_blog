<?php

namespace App\Service;

use Swift_DependencyException;
use Swift_Mailer;
use Swift_SmtpTransport;

class MailerService
{
    private Swift_Mailer $mailer;
    
    public function __construct(string $host, int $port, private string $template, private array $options)
    {
        $transport = new Swift_SmtpTransport($host, $port);
        $this->mailer = new Swift_Mailer($transport);
    }
    
    /**
     * @param string $email
     * @param $twig
     * @param string $subject
     * @return void
     * @throws Swift_DependencyException
     */
    public function sendEmail(string $email, $twig, string $subject): void
    {
        $message = (new \Swift_Message($subject))
            ->setFrom('no-reply@project5.fr')
            ->setTo($email)
            ->setBody($twig->render($this->template,
                $this->options),
                'text/html'
            );
        
        $this->mailer->send($message);
    }
}