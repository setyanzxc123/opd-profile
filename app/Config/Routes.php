<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('profil', 'Pages::profil');
$routes->get('layanan', 'Pages::layanan');
$routes->get('berita', 'Pages::berita');
$routes->get('search/berita', 'Pages::beritaSearch');
$routes->get('berita/(:segment)', 'Pages::beritaDetail/$1');
$routes->get('galeri', 'Pages::galeri');
$routes->get('dokumen', 'Pages::dokumen');
$routes->get('kontak', 'Pages::kontak');
$routes->post('kontak', 'ContactController::submit');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin',
    'filter'    => 'admin',
], function ($routes) {
    $routes->get('/', 'Dashboard::index');
    // Account settings
    $routes->get('settings', 'Account::edit');
    $routes->post('settings', 'Account::update');
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
    // Contact Messages
    $routes->get('contacts', 'Contacts::index');
    $routes->get('contacts/(:num)', 'Contacts::show/$1');
    $routes->post('contacts/(:num)/status', 'Contacts::updateStatus/$1');
    $routes->post('contacts/bulk/status', 'Contacts::bulkUpdateStatus');
    // Documents CMS
    $routes->get('documents', 'Documents::index');
    $routes->get('documents/create', 'Documents::create');
    $routes->post('documents', 'Documents::store');
    $routes->get('documents/edit/(:num)', 'Documents::edit/$1');
    $routes->post('documents/update/(:num)', 'Documents::update/$1');
    $routes->post('documents/delete/(:num)', 'Documents::delete/$1');
    // Users management (admin only)
    $routes->get('users', 'Users::index');
    $routes->get('users/create', 'Users::create');
    $routes->post('users', 'Users::store');
    $routes->get('users/edit/(:num)', 'Users::edit/$1');
    $routes->post('users/update/(:num)', 'Users::update/$1');
    $routes->post('users/toggle/(:num)', 'Users::toggle/$1');
    $routes->post('users/reset/(:num)', 'Users::resetPassword/$1');
    $routes->get('logs', 'ActivityLogs::index');
});

