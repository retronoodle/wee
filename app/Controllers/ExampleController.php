<?php

namespace App\Controllers;

use App\Request;
use App\Response;

class ExampleController {

    /**
     * Example: Using Request object
     */
    public function testRequest(Request $request) {
        $name = $request->input('name', 'Guest');
        $page = $request->query('page', 1);

        return (new Response())->json([
            'name' => $name,
            'page' => $page,
            'method' => $request->method(),
            'is_ajax' => $request->isAjax()
        ]);
    }

    /**
     * Example: Using Response object
     */
    public function testResponse() {
        return (new Response())
            ->setContent('Hello from Response!')
            ->status(200)
            ->header('X-Custom-Header', 'Wee Framework');
    }

    /**
     * Example: JSON response
     */
    public function testJson() {
        return (new Response())->json([
            'message' => 'Success',
            'framework' => 'Wee'
        ], 201);
    }

    /**
     * Example: Redirect
     */
    public function testRedirect() {
        return (new Response())->redirect('/');
    }

    /**
     * Example: Cookie
     */
    public function testCookie() {
        return (new Response())
            ->setContent('Cookie set!')
            ->cookie('test_cookie', 'cookie_value', time() + 3600);
    }
}
