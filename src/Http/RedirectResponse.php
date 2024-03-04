<?php

namespace App\Http;

use JetBrains\PhpStorm\NoReturn;

class RedirectResponse extends Response
{
    private string $url;
    private array $headers;
    
    protected string $targetUrl;
    
    public function __construct(string $url, array $headers = [])
    {
        parent::__construct('', $headers);
        $this->url = $url;
        $this->setTargetUrl($url);
        
    }
    
    public function send(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }
    }
    
    public function setTargetUrl(string $url): static
    {
        if ('' === $url) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }
        
        
        $this->targetUrl = $url;
        
        $this->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=\'%1$s\'" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, \ENT_QUOTES, 'UTF-8')));
        
        header('Location: ' . $this->url);
        
        return $this;
    }
}