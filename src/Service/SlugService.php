<?php

namespace App\Service;

class SlugService
{
    
    public function __construct(private string $slug, private readonly object $repository, private readonly object $entity)
    {
        $this->slug = $this->slugify($this->slug);
    }
    
    /**
     * @param string $text
     * @return string
     */
    private function slugify(string $text): string
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
    
    /**
     * @param $entityId
     * @return void
     */
    public function updateSlug($entityId = null): void
    {
        $existing = ($entityId) ? $this->repository->findBySlug($this->slug) : $this->repository->findBySlug($this->slug, 'create');
        
        if ($existing !== null && ($entityId === null || $existing->getId() !== $entityId)) {
            $counter = 1;
            
            $newSlug = "";
            
            while ($existing !== null && ($entityId === null || $existing->getId() !== $entityId)) {
                $newSlug = $this->slug . '-' . $counter;
                $existing = ($entityId) ? $this->repository->findBySlug($newSlug) : $this->repository->findBySlug($newSlug, 'create');
                $counter++;
            }
            
            $this->slug = $newSlug;
        }
        
        $this->entity->setSlug($this->slug);
    }
}