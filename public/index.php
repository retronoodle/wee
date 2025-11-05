<?php

/**
 * Wee Framework - Entry Point
 */

// Load the framework
require __DIR__ . '/../wee.php';

// Initialize the framework
wee::init();

// Load routes
require __DIR__ . '/../routes/web.php';

// Run the application
wee::run();
