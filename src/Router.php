<?php
namespace App;

use App\Core\Container;

class Router
{
    private array $routes = [];

    public function __construct(
        protected Container $container
    ) {}

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
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if project is in a subfolder
        $basePath = '/routing-test/public';
        if (str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        // Normalize trailing slash
        $path = rtrim($path, '/') ?: '/';

        if (!isset($this->routes[$httpMethod])) {
            http_response_code(404);
            return '404 Not Found';
        }

        // Static routes
        if (isset($this->routes[$httpMethod][$path])) {
            return $this->executeCallback(
                $this->routes[$httpMethod][$path],
                []
            );
        }

        // Dynamic routes
        foreach ($this->routes[$httpMethod] as $route => $callback) {
            $pattern = preg_replace('#\{[\w]+\}#', '([\w-]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                preg_match_all('#\{([\w]+)\}#', $route, $paramNames);
                $params = array_combine($paramNames[1], $matches);

                return $this->executeCallback($callback, $params);
            }
        }

        http_response_code(404);
        return '404 Not Found';
    }

    private function executeCallback(callable|array $callback, array $params): mixed
    {
        // Controller callback
        if (is_array($callback)) {
            [$class, $method] = $callback;

            // ✅ Use container instead of new
            $controller = $this->container->get($class);

            return $controller->$method($params);
        }

        // Closure callback
        return $callback($params);
    }
}
