<?php
//Router.php
class Router
{
    protected array $routes = [];

    public function define(array $routes): void
    {
        $this->routes = $routes;
    }

    public function direct(string $uri)
    {
        return $this->routes[$uri] ?? null;
    }
}
?>