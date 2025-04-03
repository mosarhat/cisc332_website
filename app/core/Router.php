<!-- app/core/Router.php -->

<?php
class Router {
    private $routes = [];
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function add($route, $controller, $action) {
        $this->routes[$route] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch($url) {
        if (array_key_exists($url, $this->routes)) {
            $controller = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];
            
            require_once "../app/controllers/$controller.php";
            $controllerClass = new $controller($this->database);
            $controllerClass->$action();
        } else {
            // 404 handling
            require_once "../app/views/404.php";
        }
    }
}