<?php


namespace App\Router;

class Route
{
    private string $path;
    private mixed $callable;
    private array $matches;
    
    private array $params;
    
    public function __construct($path, $callable)
    {
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }
    
    public function with($param, $regex): static
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);
        
        return $this;
    }
    
    public function match($url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->path);
        $regex = "#^$path$#i";
        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        array_shift($matches);
        $this->matches = $matches;
        
        return true;
    }
    
    public function call(): mixed
    {
        return call_user_func_array($this->getCallable(), $this->matches);
    }
    
    private function getCallable(): array|callable
    {
        if (is_string($this->callable)) {
            return $this->getControllerCallable();
        }
        
        return $this->callable;
    }
    
    private function getControllerCallable(): array
    {
        $controllerAndMethodNames = explode('#', $this->callable);
        $controllerClass = "App\\Controller\\" . $controllerAndMethodNames[0] . "Controller";
        
        return [new $controllerClass(), $controllerAndMethodNames[1]];
    }
    
    public function getUrl($params): array|string
    {
        $path = $this->path;
        
        foreach ($params as $k => $param) {
            $path = str_replace(":$k", $param, $path);
        }
        
        return $path;
    }
    
    private function paramMatch($match): string
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }
        
        return '([^/]+)';
    }
    
}