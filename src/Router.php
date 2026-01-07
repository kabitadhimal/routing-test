<?php
namespace App;

use App\Core\Container;

class Router
{
    private array $routes = [];

    public function __construct(
        protected Container $container,
        protected string $basePath = ''
    ) {}

    public function get(string $path, callable|array $callback): void
    {
        // Normalize trailing slash in registered route
        $path = rtrim($path, '/') ?: '/';
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, callable|array $callback): void
    {
        // Normalize trailing slash in registered route
        $path = rtrim($path, '/') ?: '/';
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve(): mixed
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if project is in a subfolder (configurable)
        $basePath = $this->basePath !== '' ? $this->basePath : (dirname($_SERVER['SCRIPT_NAME']) ?: '');
        if ($basePath && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        var_dump($basePath, $path);

    

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

    private function executeCallback(callable|array $callback, array $routeParams): mixed
    {
        // Controller callback
        if (is_array($callback)) {
            [$class, $method] = $callback;

            // Resolve controller from container
            $controller = $this->container->get($class);

            //Reflect Method
            $reflection = new \ReflectionMethod($controller, $method);
            $arguments = [];

            foreach ($reflection->getParameters() as $params) {
                    $name = $params->getName();
                    $type = $params->getType();

                    // class dependency (UserRepo, DummyClassA, etc.)
                    if($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                        $arguments[] = $this->container->get($type->getName());
                        continue;
                    }

                    //route parameter by name
                    if(isset($routeParams[$name])) {
                        $value = $routeParams[$name];
                        //Cast scar types
                        if($type instanceof \ReflectionNamedType) {
                            settype($value, $type->getName());
                        }

                        $arguments[] = $value;
                        continue;
                    }

                    // Default value (optional parameter)
                    if ($params->isDefaultValueAvailable()) {
                        $arguments[] = $params->getDefaultValue();
                         continue;
                    }

                    // Unable to resolve the parameter
                    throw new \Exception(
                        "Cannot resolve parameter \${$name} in {$class}::{$method}"
                    );
            }

            return $reflection->invokeArgs($controller, $arguments);
        }

        // Closure callback
        return $callback($routeParams);
    }
}
