<?php
namespace App\Core;
final class Router {
    private array $routes = [];
    public function get(string $route, array $handler): void { $this->routes['GET'][$route] = $handler; }
    public function post(string $route, array $handler): void { $this->routes['POST'][$route] = $handler; }
    public function dispatch(string $method, string $route): void {
        $handler = $this->routes[$method][$route] ?? null;
        if (!$handler) {
            $known = isset($this->routes['GET'][$route]) || isset($this->routes['POST'][$route]);
            http_response_code($known ? 405 : 404);
            if ($known) header('Allow: '.(isset($this->routes['GET'][$route]) ? 'GET, HEAD' : 'POST'));
            \render('error', ['pageTitle'=>$known?'Method not allowed':'Page not found','errorMessage'=>$known?'Use the page form to perform this action.':'We could not find that page.']);
            return;
        }
        if ($method === 'POST') \csrf_verify();
        [$class, $action] = $handler;
        (new $class)->$action();
    }
}
