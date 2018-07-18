<?php

/**
 * Routes set
 *
 * You can use parameters masks to pass them into Controller methods:
 *
 *      /api/update/{id}
 *
 * The request object can be injected into methods as well using DI
 *
 */

$routes = new \Classes\Collection\RoutesCollection([

    \Classes\Routing\Route::get('/', 'Controllers\IndexController@index'),
    \Classes\Routing\Route::get('/api/list', 'Controllers\Api\IntervalController@index'),
    \Classes\Routing\Route::post('/api/create', 'Controllers\Api\IntervalController@create'),
    \Classes\Routing\Route::get('/api/read/{id}', 'Controllers\Api\IntervalController@read'),
    \Classes\Routing\Route::post('/api/update/{id}', 'Controllers\Api\IntervalController@update'),
    \Classes\Routing\Route::get('/api/delete/{id}', 'Controllers\Api\IntervalController@delete'),

]);

return $routes;
