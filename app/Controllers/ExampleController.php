<?php

namespace App\Controllers;

use App\Controller;
use App\Request;

class ExampleController extends Controller {

    /**
     * Example: Using Request object (auto-injected)
     */
    public function testRequest(Request $request) {
        $name = $request->input('name', 'Guest');
        $page = $request->query('page', 1);

        return $this->json([
            'name' => $name,
            'page' => $page,
            'method' => $request->method(),
            'is_ajax' => $request->isAjax()
        ]);
    }

    /**
     * Example: Using helper methods
     */
    public function testResponse() {
        return $this->status(200)
            ->setContent('Hello from Controller!')
            ->header('X-Custom-Header', 'Wee Framework');
    }

    /**
     * Example: JSON response using helper
     */
    public function testJson() {
        return $this->json([
            'message' => 'Success',
            'framework' => 'Wee'
        ], 201);
    }

    /**
     * Example: Redirect using helper
     */
    public function testRedirect() {
        return $this->redirect('/');
    }

    /**
     * Example: Route parameters with dependency injection
     */
    public function showUser(Request $request, $id) {
        return $this->json([
            'id' => $id,
            'all_query_params' => $request->all()
        ]);
    }
}
