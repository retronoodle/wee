<?php

namespace App;

/**
 * Response - Handles HTTP responses
 */
class Response {

    private $content = '';
    private $statusCode = 200;
    private $headers = [];
    private $cookies = [];

    /**
     * Set response content
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response content
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Set status code
     */
    public function status($code) {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get status code
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Set header
     */
    public function header($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set cookie
     */
    public function cookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true) {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        ];
        return $this;
    }

    /**
     * Create JSON response
     */
    public function json($data, $statusCode = 200) {
        $this->statusCode = $statusCode;
        $this->headers['Content-Type'] = 'application/json';
        $this->content = json_encode($data);
        return $this;
    }

    /**
     * Create redirect response
     */
    public function redirect($url, $statusCode = 302) {
        $this->statusCode = $statusCode;
        $this->headers['Location'] = $url;
        return $this;
    }

    /**
     * Redirect back to previous page
     */
    public function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    /**
     * Create view response
     */
    public function view($template, $data = []) {
        $viewPath = __DIR__ . '/../views/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View '$template' not found");
        }

        ob_start();
        extract($data);
        require $viewPath;
        $this->content = ob_get_clean();

        return $this;
    }

    /**
     * Send the response
     */
    public function send() {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Set cookies
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        // Send content
        echo $this->content;

        return $this;
    }
}
