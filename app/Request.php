<?php

namespace App;

/**
 * Request - Handles HTTP request data
 */
class Request {

    private $query = [];
    private $post = [];
    private $files = [];
    private $cookies = [];
    private $server = [];
    private $headers = [];

    public function __construct() {
        $this->query = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->server = $_SERVER;
        $this->headers = $this->parseHeaders();
    }

    /**
     * Parse HTTP headers from $_SERVER
     */
    private function parseHeaders() {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get input value (from query or post)
     */
    public function input($key, $default = null) {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }
        return $default;
    }

    /**
     * Get query string value
     */
    public function query($key, $default = null) {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get POST value
     */
    public function post($key, $default = null) {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all input (query + post)
     */
    public function all() {
        return array_merge($this->query, $this->post);
    }

    /**
     * Check if input key exists
     */
    public function has($key) {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }

    /**
     * Get uploaded file
     */
    public function file($key) {
        return $this->files[$key] ?? null;
    }

    /**
     * Get HTTP method
     */
    public function method() {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get request URI
     */
    public function uri() {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Get request path (without query string)
     */
    public function path() {
        return parse_url($this->uri(), PHP_URL_PATH);
    }

    /**
     * Check if request path matches pattern
     */
    public function is($pattern) {
        $path = '/' . trim($this->path(), '/');
        $pattern = '/' . trim($pattern, '/');

        // Convert wildcard to regex
        $pattern = str_replace('*', '.*', $pattern);

        return preg_match('#^' . $pattern . '$#', $path) === 1;
    }

    /**
     * Get header value
     */
    public function header($key, $default = null) {
        $key = strtoupper(str_replace('-', '_', $key));
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get cookie value
     */
    public function cookie($key, $default = null) {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax() {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    /**
     * Check if request is JSON
     */
    public function isJson() {
        return strpos($this->header('Content-Type', ''), 'application/json') !== false;
    }

    /**
     * Get JSON input
     */
    public function json() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
}
