<?php

/**
 * Wee Framework
 * A tiny PHP framework with a passion for simplicity but very powerful.
 */
class wee {

    private static $instance;
    private static $config = [];
    private static $routes = [];
    private static $errorHandler;

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
     * Run the application
     */
    public static function run() {
        // To be implemented in routing phase
        echo "Wee Framework is running!";
    }
}
