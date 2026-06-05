<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Halaman utama dan halaman publik
$routes->get('/', 'Home::index');
$routes->get('peta', 'Home::map');
$routes->get('cari-terdekat', 'Home::nearest');
$routes->get('tentang', 'Home::about');
$routes->get('service-center/(:segment)', 'Home::detail/$1');

$routes->get('login', 'AdminAuthController::login');
$routes->post('login', 'AdminAuthController::attempt');
$routes->get('admin/logout', 'AdminAuthController::logout');

$routes->group('admin', ['filter' => 'adminauth'], static function ($routes) {
    $routes->get('dashboard', 'Home::adminDashboard');
    $routes->get('kelola-service-center', 'Home::manageCenters');
    $routes->get('analisis-spasial', 'Home::spatialAnalysis');
    $routes->get('kelola-brand', 'Home::manageBrands');
    $routes->get('kelola-kecamatan', 'Home::manageDistricts');
});

// ============================================================
// REST API - Service Center
// ============================================================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->get('service-center', 'ServiceCenterController::index');
    $routes->get('service-center/search', 'ServiceCenterController::search');
    $routes->get('service-center/filter', 'ServiceCenterController::filter');
    $routes->get('service-center/nearest', 'ServiceCenterController::nearest');
    $routes->get('service-center/(:segment)', 'ServiceCenterController::show/$1');
    $routes->get('brands', 'CatalogController::brands');
    $routes->get('districts', 'CatalogController::districts');
    $routes->get('city-boundary', 'CatalogController::cityBoundary');
});

$routes->group('admin-api', ['namespace' => 'App\Controllers\Api', 'filter' => 'adminauth'], static function ($routes) {
    $routes->post('service-center', 'ServiceCenterController::store');
    $routes->post('service-center/(:segment)', 'ServiceCenterController::update/$1');
    $routes->delete('service-center/(:segment)', 'ServiceCenterController::delete/$1');
    $routes->post('brands', 'CatalogController::storeBrand');
    $routes->post('brands/(:segment)', 'CatalogController::updateBrand/$1');
    $routes->delete('brands/(:segment)', 'CatalogController::deleteBrand/$1');
    $routes->post('districts', 'CatalogController::storeDistrict');
    $routes->post('districts/(:segment)', 'CatalogController::updateDistrict/$1');
    $routes->delete('districts/(:segment)', 'CatalogController::deleteDistrict/$1');
});
