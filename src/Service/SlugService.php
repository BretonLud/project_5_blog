<?php

namespace App\Service;

class SlugService
{
    
    public function __construct(private string $slug, private readonly object $repository, private readonly object $entity)
    {
        $this->slug = $this->slugify($this->slug);
    }
    
    private function slugify($text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }
    
    public function updateSlug(): void
    {
        $existing = $this->repository->findBySlug($this->slug);
        
        if ($existing !== null && $existing->getId() !== $this->entity->getId()) {
            $counter = 2;
            
            $newSlug = "";
            
            while ($existing !== null) {
                $newSlug = $this->slug . '-' . $counter;
                $existing = $this->repository->findBySlug($newSlug);
                $counter++;
            }
            
            $this->slug = $newSlug;
        }
        
        $this->entity->setSlug($this->slug);
    }
}