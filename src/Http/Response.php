<?php

namespace App\Http;

class Response
{
    protected string $content;
    private array $headers;
    
    public function __construct($content, $headers = [])
    {
        $this->setContent($content);
        $this->headers = $headers;
    }
    
    /**
     * @return void
     */
    public function send(): void
    {
        foreach ($this->headers as $header) {
            header($header);
        }
        
        echo $this->content;
    }
    
    public function setContent(?string $content): static
    {
        $this->content = $content ?? '';
        
        return $this;
    }
    
    public function getContent(): string|false
    {
        return $this->content;
    }
}