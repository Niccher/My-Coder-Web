<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Chat::index');

// Auth UI Skeleton Routes
$routes->get('login', 'Auth\AuthController::login');
$routes->get('register', 'Auth\AuthController::register');
$routes->get('forgot', 'Auth\AuthController::forgotPassword');
$routes->get('reset', 'Auth\AuthController::resetPassword');

// Debug Routes for viewing Error Pages
$routes->group('debug', static function ($routes) {
    $routes->get('400', static fn() => view('errors/html/error_400', ['message' => 'Invalid parameters sent.']));
    $routes->get('401', static fn() => view('errors/html/error_401', ['message' => 'Session expired.']));
    $routes->get('403', static fn() => view('errors/html/error_403', ['message' => 'Access to this resource requires admin privileges.']));
    $routes->get('404', static fn() => view('errors/html/error_404', ['message' => 'The controller was not found.']));
    $routes->get('500', static fn() => view('errors/html/production', ['message' => 'Database connection failed.']));
});

