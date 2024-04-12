<?php

namespace App\Router;

use App\Http\RedirectResponse;
use App\Http\Response;

class Router
{
    private array $routes;
    private string $url;
    
    public function __construct(string $url)
    {
        $this->url = $url;
    }
    
    public function get(string $path,mixed $callable): Route
    {
        return $this->add($path, $callable,  'GET');
    }
    
    public function post(string $path,mixed $callable): Route
    {
        return $this->add($path, $callable, 'POST');
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
                $response = $route->call();
                
                if ($response instanceof Response) {
                    
                    $response->send();
                }
                
                return $response;
            }
        }
        
        throw new RouterException('No matching routes', 404);
    }
    
    private function add(string $path,mixed $callable,string $method): Route
    {
        $route = new Route($path, $callable);
        
        $this->routes[$method][] = $route;
        
        return $route;
    }
}