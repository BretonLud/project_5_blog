<?php

namespace App\Router;

class Router
{
    private array $routes;
    private string $url;
    private array $namedRoutes;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    
    public function get($path, $callable, $name = null): Route
    {
        return $this->add($path, $callable, $name, 'GET');
    }
    
    public function post($path, $callable, $name = null): Route
    {
        return $this->add($path, $callable, $name, 'POST');
    }
    
    /**
     * @throws RouterException
     */
    public function run()
    {
        if (!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('REQUEST_METHOD does not exist');
        }
        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->url)) {
                return $route->call();
            }
        }
        
        throw new RouterException('No matching routes');
    }
    
    /**
     * @throws RouterException
     */
    public function url($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouterException('No route found with name: ' . $name);
        }
        
        return $this->namedRoutes[$name]->getUrl[$params];
    }
    
    private function add($path, $callable, $name, $method): Route
    {
        $route = new Route($path, $callable);
        
        $this->routes[$method][] = $route;
        
        if (is_string($callable) && $name === null) {
            $name = $callable;
        }
        
        if ($name) {
            $this->namedRoutes[$name] = $route;
        }
        
        return $route;
    }
}