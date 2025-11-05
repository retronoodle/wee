<?php

namespace App\Controllers;

/**
 * Example Home Controller
 */
class HomeController {

    public function index() {
        return 'Home page from controller';
    }

    public function show($id) {
        return "Showing item #$id";
    }
}
