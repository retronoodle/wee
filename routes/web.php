<?php

/**
 * Web Routes
 * Define your application routes here
 */

// Simple closure route
wee::get('/', function() {
    return 'Welcome to Wee Framework!';
});

// Route with parameters
wee::get('/hello/:name', function($name) {
    return "Hello, $name!";
});

// Controller routes (testing Phase 2.1)
wee::get('/home', 'HomeController@index');
wee::get('/home/:id', 'HomeController@show');
wee::get('/test/request', 'ExampleController@testRequest');
wee::get('/test/json', 'ExampleController@testJson');
wee::get('/test/user/:id', 'ExampleController@showUser');

// Named route
// wee::get('/dashboard', 'DashboardController@index')->name('dashboard');

// Route group with prefix
// wee::group(['prefix' => '/api'], function() {
//     wee::get('/users', 'UserController@index');
//     wee::get('/posts', 'PostController@index');
// });

// RESTful resource
// wee::resource('/posts', 'PostController');
