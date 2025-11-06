<?php

namespace App\Controllers;

use App\Controller;

/**
 * Example Home Controller
 */
class HomeController extends Controller {

    public function index() {
        return 'Home page from controller';
    }

    public function show($id) {
        return "Showing item #$id";
    }
}
