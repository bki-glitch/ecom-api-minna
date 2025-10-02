<?php
namespace app\core;

class Router {
    private $routes = [];
    public function get($path, $handler) { $this->add('GET', $path, $handler); }
    public function post($path, $handler) { $this->add('POST', $path, $handler); }
    public function put($path, $handler) { $this->add('PUT', $path, $handler); }
    public function delete($path, $handler) { $this->add('DELETE', $path, $handler); }
    private function add($method, $path, $handler) {
        $this->routes[] = compact('method', 'path', 'handler');
    }
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        // Remove base path if app is in a subdirectory
        $scriptName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        if (strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName) - 1);
        }
        foreach ($this->routes as $route) {
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route['path']);
            if ($method === $route['method'] && preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];
                $req = (object)['params' => $matches, 'body' => json_decode(file_get_contents('php://input'), true)];
                $res = new class {
                    public function json($data) {
                        header('Content-Type: application/json');
                        echo json_encode($data);
                    }
                };
                call_user_func($handler, $req, $res);
                return;
            }
        }
        http_response_code(404); echo 'Not Found';
    }
}
