<?php
namespace App;

class Router
{
    private array $routes = [];
    
    public function get(string $path, callable|array $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }
    
    public function post(string $path, callable|array $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }
  
    public function resolve(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if project is in a subfolder
        $basePath = '/routing-test/public';
        if (str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        // Normalize trailing slash
        $path = rtrim($path, '/') ?: '/';

        if (!isset($this->routes[$method])) {
            http_response_code(404);
            return "404 Not Found";
        }

        // Try to match static routes first
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            return $this->executeCallback($callback, []);
        }

        // Check for dynamic routes like /users/{id}
        foreach ($this->routes[$method] as $route => $callback) {
            // Convert route to regex: /users/{id} => /users/([\w-]+)
            $pattern = preg_replace('#\{[\w]+\}#', '([\w-]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // remove full match

                // Extract parameter names from the route
                preg_match_all('#\{([\w]+)\}#', $route, $paramNames);
                $params = array_combine($paramNames[1], $matches);

                return $this->executeCallback($callback, $params);
            }
        }

        http_response_code(404);
        return "404 Not Found";
    }


    private function executeCallback($callback, array $params) {
        if(is_array($callback)) {
            [$class, $method] = $callback;
            $controller = new $class();
             return $controller->$method($params);
        }

        return $callback($params);
    }
}