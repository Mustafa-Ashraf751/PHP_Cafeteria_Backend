<?php

namespace App\Routers;

class Router
{
  private $routes = [];
  private $notFoundCallback;
  private $basePath = '';

  public function __construct($basePath = '')
  {
    $this->basePath = $basePath;
  }

  public function get($route, $controller, $action)
  {
    $this->addRoute('GET', $route, $controller, $action);
    return $this;
  }

  public function post($route, $controller, $action)
  {
    $this->addRoute('POST', $route, $controller, $action);
    return $this;
  }

  public function put($route, $controller, $action)
  {
    $this->addRoute('PUT', $route, $controller, $action);
    return $this;
  }

  public function delete($route, $controller, $action)
  {
    $this->addRoute('DELETE', $route, $controller, $action);
    return $this;
  }

  public function patch($route, $controller, $action)
  {
    $this->addRoute('PATCH', $route, $controller, $action);
    return $this;
  }

  private function addRoute($method, $route, $controller, $action)
  {
    // Make sure route starts with /
    if (substr($route, 0, 1) !== '/') {
      $route = '/' . $route;
    }

    $this->routes[] = [
      'method' => $method,
      'route' => $route,
      'controller' => $controller,
      'action' => $action
    ];
  }

  public function setNotFoundHandler($callback)
  {
    $this->notFoundCallback = $callback;
    return $this;
  }

  public function dispatch()
  {
    // Get the request URI and method
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    // Handle preflight requests for CORS
    if ($method === 'OPTIONS') {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
      header('Access-Control-Allow-Headers: Content-Type, Authorization');
      http_response_code(200);
      exit;
    }

    // Remove base path from URI if set
    if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
      $uri = substr($uri, strlen($this->basePath));
    }

    // If URI is empty, set it to root
    if (empty($uri)) {
      $uri = '/';
    }

    // Find matching route
    foreach ($this->routes as $route) {
      if ($route['method'] !== $method) {
        continue;
      }

      $pattern = $this->getRoutePattern($route['route']);
      if (preg_match($pattern, $uri, $matches)) {
        // Remove the full match
        array_shift($matches);

        // Convert numeric strings to integers
        $params = array_map(function ($param) {
          return is_numeric($param) ? (int)$param : $param;
        }, $matches);

        // Create controller instance
        $controllerClass = $route['controller'];
        $controller = new $controllerClass();

        // Call the action with parameters
        call_user_func_array([$controller, $route['action']], $params);
        return;
      }
    }

    // No route matched - handle 404
    if (is_callable($this->notFoundCallback)) {
      call_user_func($this->notFoundCallback);
    } else {
      header('HTTP/1.1 404 Not Found');
      echo json_encode(['error' => 'Route not found']);
    }
  }

  private function getRoutePattern($route)
  {
    // Replace route placeholders like {id} with regex pattern for capturing
    $pattern = preg_replace('/{([^\/]+)}/', '([^/]+)', $route);

    // Add start and end markers for exact match
    $pattern = '#^' . $pattern . '$#';

    return $pattern;
  }
}
