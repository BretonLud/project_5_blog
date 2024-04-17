<?php


namespace App\Router;

class Route
{
    private string $path;
    private mixed $callable;
    private array $matches;
    
    private array $params;
    
    public function __construct(string $path,mixed $callable)
    {
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }
    
    public function with(string $param,string $regex): static
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);
        
        return $this;
    }
    
    public function match(string $url): bool
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
        $controllerAndMethodNamesAndRepository = explode('#', $this->callable);
        $controller = $controllerAndMethodNamesAndRepository[0];
        $method = $controllerAndMethodNamesAndRepository[1];
        $controllerClass = "App\\Controller\\" . $controller . "Controller";
        
        return [new $controllerClass(), $method];
    }
    
    public function getUrl(array $params): array|string
    {
        $path = $this->path;
        
        foreach ($params as $k => $param) {
            $path = str_replace(":$k", $param, $path);
        }
        
        return $path;
    }
    
    private function paramMatch(array $match): string
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }
        
        return '([^/]+)';
    }
    
}