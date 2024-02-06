<?php

namespace App\Http;

class Response
{
    private string $content;
    private array $headers;
    
    public function __construct($content, $headers = [])
    {
        $this->content = $content;
        $this->headers = $headers;
    }
    
    public function send(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }
        echo $this->content;
    }
}