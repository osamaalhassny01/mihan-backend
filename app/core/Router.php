<?php
/**
 * Router - simple front-controller style router.
 * Supports GET/POST with dynamic {param} segments, mapped to Controller@method.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, string $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');
        $base = BASE_PATH;
        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '' ) $uri = '/';
        $uri = rtrim($uri, '/');
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#u';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerName, $methodName] = explode('@', $route['handler']);
                $controllerFile = __DIR__ . "/../controllers/{$controllerName}.php";
                if (!file_exists($controllerFile)) {
                    $this->abort(500, "Controller not found: $controllerName");
                }
                require_once $controllerFile;
                $controller = new $controllerName();
                if (!method_exists($controller, $methodName)) {
                    $this->abort(500, "Method not found: $controllerName@$methodName");
                }
                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }
        $this->abort(404);
    }

    private function abort(int $code, string $msg = ''): void
    {
        http_response_code($code);
        $file = __DIR__ . "/../views/errors/{$code}.php";
        if (file_exists($file)) {
            require $file;
        } else {
            echo "Error $code $msg";
        }
        exit;
    }
}
