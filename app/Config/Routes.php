<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/search', 'SearchController::index');
$routes->get('/leaderboard', 'LeaderboardController::index');
$routes->get('/creator/(:num)', 'SearchController::creator/$1');
$routes->post('/creator/(:num)/follow', 'SocialController::follow/$1');
$routes->get('/course/(:num)', 'Home::course/$1');
$routes->get('/notifications', 'SocialController::notifications');
$routes->post('/notifications/read', 'SocialController::markNotificationsRead');

$routes->post('/course/(:num)/bookmark', 'LearningController::bookmark/$1');
$routes->post('/course/(:num)/progress', 'LearningController::progress/$1');
$routes->post('/course/(:num)/comments', 'CommentController::store/$1');
$routes->get('/my-learning', 'LearningController::myLearning');

$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::store');
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::authenticate');
$routes->post('/logout', 'AuthController::logout');

$routes->get('/profile', 'ProfileController::edit');
$routes->post('/profile', 'ProfileController::update');

$routes->get('/email/verify-notice', 'AuthController::verifyNotice');
$routes->post('/email/verification-notification', 'AuthController::resendVerification');
$routes->post('/email/verify-otp', 'AuthController::verifyEmailOtp');
$routes->get('/email/verify/(:segment)', 'AuthController::verifyEmail/$1');

$routes->get('/login-otp', 'AuthController::loginOtp');
$routes->post('/login-otp', 'AuthController::verifyLoginOtp');

$routes->get('/forgot-password', 'AuthController::forgotPassword');
$routes->post('/forgot-password', 'AuthController::sendResetLink');
$routes->get('/reset-password-otp', 'AuthController::resetPasswordOtp');
$routes->post('/reset-password-otp', 'AuthController::updatePasswordWithOtp');
$routes->get('/reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('/reset-password', 'AuthController::updatePassword');

$routes->get('/dashboard', 'DashboardController::index');
$routes->post('/dashboard/problems/(:num)/reply', 'CommentController::replyProblem/$1');

$routes->get('/posts/create', 'PostController::create');
$routes->post('/posts', 'PostController::store');
$routes->get('/posts/edit/(:num)', 'PostController::edit/$1');
$routes->post('/posts/update/(:num)', 'PostController::update/$1');
$routes->post('/posts/delete/(:num)', 'PostController::delete/$1');
