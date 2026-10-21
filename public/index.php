<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Danish\PhpRouter\App\Router;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Controller\HomeController;
use Danish\IkspiTubanRegistration\Controller\ProfileController;
use Danish\IkspiTubanRegistration\Controller\RegistrationController;
use Danish\IkspiTubanRegistration\Controller\RegistrationPeriodController;
use Danish\IkspiTubanRegistration\Controller\UserAuthController;
use Danish\IkspiTubanRegistration\Controller\UserController;
use Danish\IkspiTubanRegistration\Middleware\MustAdminMiddleware;
use Danish\IkspiTubanRegistration\Middleware\MustLoginMiddleware;
use Danish\IkspiTubanRegistration\Middleware\MustNotLoginMiddleware;

$startEnv = Dotenv::createImmutable(__DIR__ . '/../');
$startEnv->load();

Database::getConnection('prod');

Router::get('/', [HomeController::class, 'home']);
Router::get('/about', [HomeController::class, 'about'], [MustNotLoginMiddleware::class]);

Router::get('/login', [UserAuthController::class, 'login'], [MustNotLoginMiddleware::class]);
Router::post('/login', [UserAuthController::class, 'postLogin'], [MustNotLoginMiddleware::class]);

Router::get('/register', [UserAuthController::class, 'register'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);
Router::post('/register', [UserAuthController::class, 'postRegister'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);

Router::get('/user/all', [UserController::class, 'allUser'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);
Router::post('/user/delete', [UserController::class, 'postDelete'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);

Router::post('/logout', [UserAuthController::class, 'postLogout'], [MustLoginMiddleware::class]);

Router::get('/profile', [ProfileController::class, 'save'], [MustLoginMiddleware::class]);
Router::post('/profile/save', [ProfileController::class, 'postSave'], [MustLoginMiddleware::class]);

Router::get('/auth/password', [UserAuthController::class, 'changePassword'], [MustLoginMiddleware::class]);
Router::post('/auth/password', [UserAuthController::class, 'postChangePassword'], [MustLoginMiddleware::class]);

Router::get('/registration/period/all', [RegistrationPeriodController::class, 'registrationPeriods'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);
Router::get('/registration/period/save', [RegistrationPeriodController::class, 'save'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);
Router::post('/registration/period/save', [RegistrationPeriodController::class, 'postSave'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);
Router::post('/registration/period/delete', [RegistrationPeriodController::class, 'postDelete'], [MustLoginMiddleware::class, MustAdminMiddleware::class]);

Router::get('/registration', [RegistrationController::class, 'registration'], [MustLoginMiddleware::class]);


Router::run();