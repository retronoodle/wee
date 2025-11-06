<?php

/**
 * Wee Framework
 * A tiny PHP framework with a passion for simplicity but very powerful.
 */
class wee {

    private static $instance;
    private static $config = [];
    private static $router;
    private static $request;
    private static $middlewares = [];
    private static $beforeMiddleware = [];
    private static $afterMiddleware = [];

    /**
     * Initialize the framework
     */
    public static function init() {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();
        self::registerAutoloader();
        self::registerErrorHandler();
        self::loadConfig();
        self::$router = new App\Router();
        self::$request = new App\Request();

        return self::$instance;
    }

    /**
     * Register PSR-4 autoloader for app classes
     */
    private static function registerAutoloader() {
        spl_autoload_register(function ($class) {
            // App namespace
            if (strpos($class, 'App\\') === 0) {
                $file = __DIR__ . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
                if (file_exists($file)) {
                    require $file;
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Register error and exception handlers
     */
    private static function registerErrorHandler() {
        // Convert errors to exceptions
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        // Handle uncaught exceptions
        set_exception_handler(function ($exception) {
            self::handleException($exception);
        });

        // Handle fatal errors
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                self::handleException(new ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                ));
            }
        });
    }

    /**
     * Handle exceptions
     */
    private static function handleException($exception) {
        $debug = self::config('app.debug', true);

        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');

        if ($debug) {
            self::displayDetailedError($exception);
        } else {
            self::displayGenericError();
        }

        exit(1);
    }

    /**
     * Display detailed error (debug mode)
     */
    private static function displayDetailedError($exception) {
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f5f5f5; }
        .error-box { background: white; border-left: 4px solid #e74c3c; padding: 20px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .error-title { color: #e74c3c; font-size: 24px; margin: 0 0 10px 0; }
        .error-message { font-size: 16px; color: #333; margin: 10px 0; }
        .error-location { color: #666; font-size: 14px; margin: 5px 0; }
        .stack-trace { background: #2c3e50; color: #ecf0f1; padding: 15px; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1 class="error-title">' . htmlspecialchars(get_class($exception)) . '</h1>
        <div class="error-message">' . htmlspecialchars($exception->getMessage()) . '</div>
        <div class="error-location">
            <strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . '<br>
            <strong>Line:</strong> ' . $exception->getLine() . '
        </div>
    </div>
    <div class="stack-trace">' . htmlspecialchars($exception->getTraceAsString()) . '</div>
</body>
</html>';
    }

    /**
     * Display generic error (production mode)
     */
    private static function displayGenericError() {
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; background: #f5f5f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .error-container { text-align: center; }
        .error-code { font-size: 72px; color: #e74c3c; margin: 0; }
        .error-message { font-size: 24px; color: #333; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">500</h1>
        <p class="error-message">Something went wrong</p>
    </div>
</body>
</html>';
    }

    /**
     * Load configuration files
     */
    private static function loadConfig() {
        $configDir = __DIR__ . '/config';

        if (!is_dir($configDir)) {
            return;
        }

        foreach (glob($configDir . '/*.php') as $file) {
            $key = basename($file, '.php');
            $config = require $file;

            if (is_array($config)) {
                self::$config[$key] = $config;
            }
        }
    }

    /**
     * Get configuration value
     *
     * @param string $key Dot notation (e.g., 'app.debug')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function config($key, $default = null) {
        $parts = explode('.', $key);
        $value = self::$config;

        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }

    /**
     * Set configuration value
     *
     * @param string $key Dot notation
     * @param mixed $value
     */
    public static function setConfig($key, $value) {
        $parts = explode('.', $key);
        $config = &self::$config;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $config[$part] = $value;
            } else {
                if (!isset($config[$part]) || !is_array($config[$part])) {
                    $config[$part] = [];
                }
                $config = &$config[$part];
            }
        }
    }

    /**
     * Get environment variable with fallback
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function env($key, $default = null) {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        // Convert string representations to actual types
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }

    /**
     * HTTP Method Routes
     */
    public static function get($path, $handler) {
        return self::$router->get($path, $handler);
    }

    public static function post($path, $handler) {
        return self::$router->post($path, $handler);
    }

    public static function put($path, $handler) {
        return self::$router->put($path, $handler);
    }

    public static function delete($path, $handler) {
        return self::$router->delete($path, $handler);
    }

    public static function patch($path, $handler) {
        return self::$router->patch($path, $handler);
    }

    public static function any($path, $handler) {
        return self::$router->any($path, $handler);
    }

    /**
     * Route groups
     */
    public static function group($attributes, $callback) {
        return self::$router->group($attributes, $callback);
    }

    /**
     * RESTful resource routing
     */
    public static function resource($path, $controller) {
        return self::$router->resource($path, $controller);
    }

    /**
     * Get named route URL
     */
    public static function route($name, $params = []) {
        return self::$router->route($name, $params);
    }

    /**
     * Register named middleware
     */
    public static function middleware($name, $callback) {
        self::$middlewares[$name] = $callback;
    }

    /**
     * Register global before middleware
     */
    public static function before($callback) {
        self::$beforeMiddleware[] = $callback;
    }

    /**
     * Register global after middleware
     */
    public static function after($callback) {
        self::$afterMiddleware[] = $callback;
    }

    /**
     * Get Request instance
     */
    public static function request() {
        return self::$request;
    }

    /**
     * Run the application
     */
    public static function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Run before middleware
        foreach (self::$beforeMiddleware as $middleware) {
            $result = $middleware();
            if ($result !== null) {
                self::sendResponse($result);
                return;
            }
        }

        // Match route
        $match = self::$router->dispatch($method, $uri);

        if ($match === null) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        // Run route middleware
        foreach ($match['middleware'] as $middlewareName) {
            if (isset(self::$middlewares[$middlewareName])) {
                $result = self::$middlewares[$middlewareName]();
                if ($result !== null) {
                    self::sendResponse($result);
                    return;
                }
            }
        }

        // Execute handler
        $response = self::executeHandler($match['handler'], $match['params']);

        // Run after middleware
        foreach (self::$afterMiddleware as $middleware) {
            $middleware($response);
        }

        // Send response
        self::sendResponse($response);
    }

    /**
     * Execute route handler (closure or controller)
     */
    private static function executeHandler($handler, $params) {
        // Closure handler
        if (is_callable($handler)) {
            return call_user_func_array($handler, array_values($params));
        }

        // Controller@method handler
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);

            // Add App namespace if not present
            if (strpos($controller, '\\') === false) {
                $controller = 'App\\Controllers\\' . $controller;
            }

            if (!class_exists($controller)) {
                throw new \Exception("Controller '$controller' not found");
            }

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new \Exception("Method '$method' not found in controller '$controller'");
            }

            // Dependency injection for controller methods
            $methodParams = self::resolveMethodDependencies($instance, $method, $params);

            return call_user_func_array([$instance, $method], $methodParams);
        }

        throw new \Exception("Invalid route handler");
    }

    /**
     * Resolve method dependencies using reflection
     */
    private static function resolveMethodDependencies($instance, $method, $routeParams) {
        $reflection = new \ReflectionMethod($instance, $method);
        $parameters = $reflection->getParameters();
        $resolved = [];

        foreach ($parameters as $param) {
            $type = $param->getType();

            // Type-hinted parameter (dependency injection)
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();

                // Inject Request instance
                if ($className === 'App\\Request' || is_subclass_of($className, 'App\\Request')) {
                    $resolved[] = self::$request;
                    continue;
                }

                // Inject Response instance
                if ($className === 'App\\Response') {
                    $resolved[] = new App\Response();
                    continue;
                }

                // Try to instantiate the class
                if (class_exists($className)) {
                    $resolved[] = new $className();
                    continue;
                }
            }

            // Route parameter
            $paramName = $param->getName();
            if (isset($routeParams[$paramName])) {
                $resolved[] = $routeParams[$paramName];
                unset($routeParams[$paramName]);
                continue;
            }

            // Default value
            if ($param->isDefaultValueAvailable()) {
                $resolved[] = $param->getDefaultValue();
                continue;
            }

            // No value available
            throw new \Exception("Cannot resolve parameter '{$paramName}' for {$method}");
        }

        return $resolved;
    }

    /**
     * Send response to client
     */
    private static function sendResponse($response) {
        // Response object
        if ($response instanceof App\Response) {
            $response->send();
            return;
        }

        // Array/object - JSON response
        if (is_array($response) || is_object($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo $response;
        }
    }

    /**
     * JSON response helper
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect helper
     */
    public static function redirect($url, $statusCode = 302) {
        header("Location: $url", true, $statusCode);
        exit;
    }

    /**
     * Create new Response instance
     */
    public static function response($content = '', $statusCode = 200) {
        return (new App\Response())
            ->setContent($content)
            ->status($statusCode);
    }

    /**
     * Create view response
     */
    public static function view($template, $data = []) {
        return (new App\Response())->view($template, $data);
    }
}
