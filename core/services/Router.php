<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\NotFoundHttpException;
use app\core\helpers\RouterHelper;
use Closure;
use Exception;
use ReflectionClass;

final class Router
{

    public const string ROUTER_DELETE = 'DELETE';
    public const string ROUTER_GET = 'GET';
    public const string ROUTER_HEAD = 'HEAD';
    public const string ROUTER_OPTIONS = 'OPTIONS';
    public const string ROUTER_PATCH = 'PATCH';
    public const string ROUTER_POST = 'POST';
    public const string ROUTER_PUT = 'PUT';

    private array $afterRoutes = [];
    private array $beforeRoutes = [];

    private string $baseRoute = '';

    public function addBeforeRoute(string $method, string $route, string|Closure $action): void
    {
        if (in_array($method, [
            self::ROUTER_DELETE,
            self::ROUTER_GET,
            self::ROUTER_HEAD,
            self::ROUTER_OPTIONS,
            self::ROUTER_PATCH,
            self::ROUTER_POST,
            self::ROUTER_PUT,
        ])) {
            $route = $this->baseRoute . '/' . trim($route, '/');
            $route = $this->baseRoute ? rtrim($route, '/') : $route;

            $this->beforeRoutes[$method][$route] = $action;
        }
    }

    public function addRoute(string $method, string $route, string|Closure $action): void
    {
        if (in_array($method, [
            self::ROUTER_DELETE,
            self::ROUTER_GET,
            self::ROUTER_HEAD,
            self::ROUTER_OPTIONS,
            self::ROUTER_PATCH,
            self::ROUTER_POST,
            self::ROUTER_PUT,
        ])) {
            $route = $this->baseRoute . '/' . trim($route, '/');
            $route = $this->baseRoute ? rtrim($route, '/') : $route;

            $this->afterRoutes[$method][$route] = $action;
        }
    }

    /**
     * @throws Exception
     */
    public function dispatch(): void
    {
        $method = App::$request->getMethod();

        if ($method === self::ROUTER_OPTIONS) {
            http_response_code(204);
            exit;
        }

        $path = App::$request->getPath();

        if ($path == '/') {
            Response::redirect('site/index');
        }

        $matchedBeforeRoute = RouterHelper::findConfiguredRoute($this->beforeRoutes, $method, $path);

        if (!empty($matchedBeforeRoute)) {
            $this->callHandler($matchedBeforeRoute['handler'], $matchedBeforeRoute['params']);
        }

        $matchedRoute = RouterHelper::findConfiguredRoute($this->afterRoutes, $method, $path);

        if (empty($matchedRoute)) {
            throw new NotFoundHttpException("Route not found: $path");
        }

        $this->callHandler($matchedRoute['handler'], $matchedRoute['params']);

        if ($method == self::ROUTER_HEAD) {
            ob_end_clean();
        }

        exit();
    }

    /**
     * @throws Exception
     */
    private function callHandler(string|Closure $handler, array $params = []): void
    {
        if (is_callable($handler)) {
            $response = call_user_func_array($handler, $params);
        } elseif (str_contains($handler, '@')) {
            list($controllerName, $actionName) = explode('@', $handler);

            $controllerName = "app\\controllers\\$controllerName";

            if (!class_exists($controllerName)) {
                throw new Exception("Controller $controllerName not found");
            }

            $ref = new ReflectionClass($controllerName);

            if (!$ref->hasMethod($actionName)) {
                throw new Exception("Action $actionName not found in $controllerName");
            }

            $instance = $ref->newInstance();

            /** @var Controller $instance */
            $response = $instance->createAction($actionName, $params);
        }

        if (!isset($response)) {
            return;
        }

        if ($response instanceof Response) {
            $response->send();
        } elseif (is_array($response) || is_object($response)) {
            Response::json($response)->send();
        } else {
            echo $response;
        }
    }

    public function mount(string $route, Closure $fn): void
    {
        $curBaseRoute = $this->baseRoute;

        $this->baseRoute .= $route;

        call_user_func($fn);

        $this->baseRoute = $curBaseRoute;
    }

    public function addRoutes(string $method, array $routes): void
    {
        foreach ($routes as $route) {
            if (!$route['route'] || !$route['action']) {
                continue;
            }

            $this->addRoute($method, $route['route'], $route['action']);
        }
    }

    public function addBeforeRoutes(string $method, array $routes): void
    {
        foreach ($routes as $route) {
            if (!$route['route'] || !$route['action']) {
                continue;
            }

            $this->addBeforeRoute($method, $route['route'], $route['action']);
        }
    }

}