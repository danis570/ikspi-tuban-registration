<?php

use Danish\IkspiTubanRegistration\Middleware\MustUserMiddleware;

session_start();
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Danish\PhpRouter\App\Router;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Controller\AdministrativeAreaController;
use Danish\IkspiTubanRegistration\Controller\AdminRegistrationController;
use Danish\IkspiTubanRegistration\Controller\HomeController;
use Danish\IkspiTubanRegistration\Controller\ProfileController;
use Danish\IkspiTubanRegistration\Controller\RegistrationController;
use Danish\IkspiTubanRegistration\Controller\RegistrationGenerateController;
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

Router::get('/registration', [RegistrationController::class, 'registration'], [MustLoginMiddleware::class, MustUserMiddleware::class]);

Router::get(
    '/registration/level/i',
    [RegistrationController::class, 'saveLevel1'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/level/i',
    [RegistrationController::class, 'postSaveLevel1'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::get(
    '/registration/level/ii',
    [RegistrationController::class, 'saveLevel2'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/level/ii',
    [RegistrationController::class, 'postSaveLevel2'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::get(
    '/api/administrative-areas/provinces',
    [AdministrativeAreaController::class, 'provinces'],
    [MustLoginMiddleware::class]
);

Router::get(
    '/api/administrative-areas/regencies',
    [AdministrativeAreaController::class, 'regencies'],
    [MustLoginMiddleware::class]
);

Router::get(
    '/api/administrative-areas/districts',
    [AdministrativeAreaController::class, 'districts'],
    [MustLoginMiddleware::class]
);

Router::get(
    '/api/administrative-areas/villages',
    [AdministrativeAreaController::class, 'villages'],
    [MustLoginMiddleware::class]
);

Router::get(
    '/registration/all',
    [RegistrationController::class, 'registrationByUserId'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::get(
    '/registration/edit',
    [RegistrationController::class, 'edit'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/edit',
    [RegistrationController::class, 'postEdit'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/delete',
    [RegistrationController::class, 'postDelete'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/generate',
    [RegistrationGenerateController::class, 'postSelected'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/generate/pdf',
    [RegistrationGenerateController::class, 'postPdf'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::post(
    '/registration/generate/excel',
    [RegistrationGenerateController::class, 'postExcel'],
    [MustLoginMiddleware::class, MustUserMiddleware::class]
);

Router::get(
    '/admin/registration/all',
    [AdminRegistrationController::class, 'index'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::get(
    '/admin/registration/edit',
    [AdminRegistrationController::class, 'edit'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::post(
    '/admin/registration/edit',
    [AdminRegistrationController::class, 'postEdit'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::post(
    '/admin/registration/delete',
    [AdminRegistrationController::class, 'postDelete'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::post(
    '/admin/registration/delete-selected',
    [AdminRegistrationController::class, 'postDeleteSelected'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::post(
    '/admin/registration/generate/pdf',
    [RegistrationGenerateController::class, 'postAdminPdf'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::post(
    '/admin/registration/generate/excel',
    [RegistrationGenerateController::class, 'postAdminExcel'],
    [MustLoginMiddleware::class, MustAdminMiddleware::class]
);

Router::run();