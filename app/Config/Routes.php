<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('profil', 'Pages::profil');
$routes->get('layanan', 'Pages::layanan');
$routes->get('berita', 'Pages::berita');
$routes->get('berita/kategori/(:segment)', 'Pages::beritaKategori/$1');
$routes->get('berita/tag/(:segment)', 'Pages::beritaTag/$1');
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
    $routes->get('profile', 'Profile::edit');
    $routes->post('profile', 'Profile::update');
    // News CMS
    $routes->get('news', 'News::index');
    $routes->get('news/create', 'News::create');
    $routes->post('news', 'News::store');
    $routes->get('news/edit/(:num)', 'News::edit/$1');
    $routes->post('news/update/(:num)', 'News::update/$1');
    $routes->post('news/store', 'News::store'); // Explicit create route
    $routes->post('news/delete/(:num)', 'News::delete/$1');
    // Services CMS
    $routes->get('services', 'Services::index');
    $routes->get('services/create', 'Services::create');
    $routes->post('services', 'Services::store');
    $routes->get('services/edit/(:num)', 'Services::edit/$1');
    $routes->post('services/update/(:num)', 'Services::update/$1');
    $routes->post('services/delete/(:num)', 'Services::delete/$1');
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
    // Hero Sliders CMS
    $routes->get('hero-sliders', 'HeroSliders::index');
    $routes->get('hero-sliders/create', 'HeroSliders::create');
    $routes->post('hero-sliders', 'HeroSliders::store');
    $routes->get('hero-sliders/edit/(:num)', 'HeroSliders::edit/$1');
    $routes->post('hero-sliders/update/(:num)', 'HeroSliders::update/$1');
    $routes->post('hero-sliders/delete/(:num)', 'HeroSliders::delete/$1');
    $routes->post('hero-sliders/duplicate/(:num)', 'HeroSliders::duplicate/$1');
    $routes->get('hero-sliders/preview/(:num)', 'HeroSliders::preview/$1');
    $routes->post('hero-sliders/sort-order', 'HeroSliders::updateSortOrder');
    $routes->post('hero-sliders/track-view/(:num)', 'HeroSliders::trackView/$1');
    $routes->get('hero-sliders/slots/news', 'HeroSliderSlots::news');
    
    // Debug routes (temporary for troubleshooting)
    $routes->get('hero-slider-debug/check-status', 'HeroSliderDebug::checkStatus');
    $routes->get('hero-slider-debug/reset-and-create', 'HeroSliderDebug::resetAndCreate');
    $routes->get('hero-slider-debug/force-create', 'HeroSliderDebug::forceCreate');
});
