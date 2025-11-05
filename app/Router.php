<?php

namespace App;

/**
 * Router - Handles HTTP routing for Wee Framework
 */
class Router {

    private $routes = [];
    private $namedRoutes = [];
    private $groupStack = [];
    private $currentRoute = null;

    /**
     * Add a route
     */
    public function addRoute($method, $path, $handler) {
        $route = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => [],
            'name' => null,
        ];

        // Apply group attributes
        if (!empty($this->groupStack)) {
            $group = end($this->groupStack);

            if (isset($group['prefix'])) {
                $route['path'] = rtrim($group['prefix'], '/') . '/' . ltrim($path, '/');
            }

            if (isset($group['middleware'])) {
                $route['middleware'] = array_merge(
                    (array)$group['middleware'],
                    $route['middleware']
                );
            }
        }

        $this->routes[] = $route;
        $this->currentRoute = &$this->routes[count($this->routes) - 1];

        return $this;
    }

    /**
     * Add GET route
     */
    public function get($path, $handler) {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add POST route
     */
    public function post($path, $handler) {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add PUT route
     */
    public function put($path, $handler) {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add DELETE route
     */
    public function delete($path, $handler) {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add PATCH route
     */
    public function patch($path, $handler) {
        return $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Add route for any HTTP method
     */
    public function any($path, $handler) {
        return $this->addRoute('ANY', $path, $handler);
    }

    /**
     * Set route name
     */
    public function name($name) {
        if ($this->currentRoute !== null) {
            $this->currentRoute['name'] = $name;
            $this->namedRoutes[$name] = $this->currentRoute;
        }
        return $this;
    }

    /**
     * Add middleware to route
     */
    public function middleware($middleware) {
        if ($this->currentRoute !== null) {
            $this->currentRoute['middleware'] = array_merge(
                $this->currentRoute['middleware'],
                (array)$middleware
            );
        }
        return $this;
    }

    /**
     * Create a route group
     */
    public function group($attributes, $callback) {
        $this->groupStack[] = $attributes;
        $callback();
        array_pop($this->groupStack);
    }

    /**
     * Create RESTful resource routes
     */
    public function resource($path, $controller) {
        $base = trim($path, '/');

        $this->get("/$base", "$controller@index")->name("$base.index");
        $this->get("/$base/create", "$controller@create")->name("$base.create");
        $this->post("/$base", "$controller@store")->name("$base.store");
        $this->get("/$base/:id", "$controller@show")->name("$base.show");
        $this->get("/$base/:id/edit", "$controller@edit")->name("$base.edit");
        $this->put("/$base/:id", "$controller@update")->name("$base.update");
        $this->delete("/$base/:id", "$controller@destroy")->name("$base.destroy");
    }

    /**
     * Get URL for named route
     */
    public function route($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route '$name' not found");
        }

        $path = $this->namedRoutes[$name]['path'];

        foreach ($params as $key => $value) {
            $path = str_replace(":$key", $value, $path);
        }

        return $path;
    }

    /**
     * Match current request to a route
     */
    public function dispatch($method, $uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            // Check HTTP method
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                continue;
            }

            // Match route pattern
            $pattern = $this->routeToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                // Extract parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return [
                    'handler' => $route['handler'],
                    'params' => $params,
                    'middleware' => $route['middleware']
                ];
            }
        }

        return null;
    }

    /**
     * Convert route path to regex pattern
     */
    private function routeToRegex($path) {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);

        // Convert :param to named capture groups
        $pattern = preg_replace('/\:([a-zA-Z0-9_]+)/', '(?P<$1>[^\/]+)', $pattern);

        return '/^' . $pattern . '$/';
    }

    /**
     * Get all routes
     */
    public function getRoutes() {
        return $this->routes;
    }
}
