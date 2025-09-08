<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin',
    'filter'    => 'admin',
], function($routes) {
    $routes->get('/', 'Dashboard::index');
    // OPD Profile CMS
    $routes->get('profile', 'Profile::edit');
    $routes->post('profile', 'Profile::update');
});

