<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $pathExistsForAnotherMethod = false;

        foreach ($this->routes as $route) {
            $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['pattern']);
            $regex = '#^' . $regex . '$#';

            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            if ($route['method'] !== strtoupper($method)) {
                $pathExistsForAnotherMethod = true;
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = rawurldecode($value);
                }
            }

            ($route['handler'])($params);
            return;
        }

        if ($pathExistsForAnotherMethod) {
            // 405 Method Not Allowed - путь существует, но для него не разрешен такой HTTP-метод.
            Response::json(['error' => 'Method not allowed'], 405);
        }

        // 404 Not Found - такой путь вообще не зарегистрирован в роутере.
        Response::json(['error' => 'Not found'], 404);
    }
}
