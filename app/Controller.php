<?php

namespace App;

/**
 * Base Controller
 * All controllers should extend this class to access helper methods
 */
class Controller {

    /**
     * @var Request
     */
    protected $request;

    /**
     * Controller constructor
     */
    public function __construct() {
        $this->request = \wee::request();
    }

    /**
     * Render a view
     *
     * @param string $template
     * @param array $data
     * @return Response
     */
    protected function view($template, $data = []) {
        return (new Response())->view($template, $data);
    }

    /**
     * Return JSON response
     *
     * @param mixed $data
     * @param int $statusCode
     * @return Response
     */
    protected function json($data, $statusCode = 200) {
        return (new Response())->json($data, $statusCode);
    }

    /**
     * Redirect to URL
     *
     * @param string $url
     * @param int $statusCode
     * @return Response
     */
    protected function redirect($url, $statusCode = 302) {
        return (new Response())->redirect($url, $statusCode);
    }

    /**
     * Redirect back to previous page
     *
     * @return Response
     */
    protected function back() {
        return (new Response())->back();
    }

    /**
     * Return response with status code
     *
     * @param int $statusCode
     * @return Response
     */
    protected function status($statusCode) {
        return (new Response())->status($statusCode);
    }

    /**
     * Validate request data
     *
     * @param Request $request
     * @param array $rules
     * @return array Validated data
     * @throws \Exception if validation fails
     */
    protected function validate(Request $request, array $rules) {
        // TODO: Implement validation in Phase 3.2
        // For now, just return all input data
        return $request->all();
    }
}
