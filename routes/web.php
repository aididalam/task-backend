<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/




$router->get('/', function () use ($router) {
    echo "<center> Welcome </center>";
});

$router->get('/version', function () use ($router) {
    return $router->app->version();
});

$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');
$router->post('register', 'AuthController@register');
$router->post('refresh', 'AuthController@refresh');
$router->get('me', 'AuthController@me');

$router->group(['prefix' => 'tasks', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/', 'TaskController@store');         // Create a task
    $router->get('/', 'TaskController@index');         // Get tasks (with filters)
    $router->put('{id}', 'TaskController@update');     // Update a task
    $router->delete('{id}', 'TaskController@destroy'); // Delete a task
});
