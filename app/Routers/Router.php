<?php

namespace App\Routers;

class Router
{
    private $routes = [];
    private $basePath = '/PHP_Cafeteria_Backend/public';

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function patch($path, $callback)
    {
        $this->routes['PATCH'][$path] = $callback;
    }

    public function delete($path, $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        $path = explode('?', $path)[0];
        
        // Remove base path if exists
        if (strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        
        if (empty($path)) {
            $path = '/';
        }

        $callback = null;
        $params = [];
        
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = "#^" . $pattern . "$#";
            
            if (preg_match($pattern, $path, $matches)) {
                $callback = $handler;
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                break;
            }
        }

        if (!$callback) {
            header("HTTP/1.0 404 Not Found");
            return "404 Not Found";
        }

        if (is_array($callback)) {
            [$class, $method] = $callback;
            $controller = new $class();
            return call_user_func_array([$controller, $method], $params);
        }

        return call_user_func_array($callback, $params);
    }
}
