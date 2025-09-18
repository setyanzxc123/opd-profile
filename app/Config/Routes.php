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
    // News CMS
    $routes->get('news', 'News::index');
    $routes->get('news/create', 'News::create');
    $routes->post('news', 'News::store');
    $routes->get('news/edit/(:num)', 'News::edit/$1');
    $routes->post('news/update/(:num)', 'News::update/$1');
    $routes->post('news/delete/(:num)', 'News::delete/$1');
    // Galleries CMS
    $routes->get('galleries', 'Galleries::index');
    $routes->get('galleries/create', 'Galleries::create');
    $routes->post('galleries', 'Galleries::store');
    $routes->get('galleries/edit/(:num)', 'Galleries::edit/$1');
    $routes->post('galleries/update/(:num)', 'Galleries::update/$1');
    $routes->post('galleries/delete/(:num)', 'Galleries::delete/$1');
    // Documents CMS
    $routes->get('documents', 'Documents::index');
    $routes->get('documents/create', 'Documents::create');
    $routes->post('documents', 'Documents::store');
    $routes->get('documents/edit/(:num)', 'Documents::edit/$1');
    $routes->post('documents/update/(:num)', 'Documents::update/$1');
    $routes->post('documents/delete/(:num)', 'Documents::delete/$1');
});

