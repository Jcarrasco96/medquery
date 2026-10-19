<?php

declare(strict_types=1);

use app\core\App;
use app\core\helpers\ArrayHelper;
use app\core\services\Router;

include 'vendor/autoload.php';

defined('APP_ENV') or define('APP_ENV', 'dev');

const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR;
const VIEWS_PATH = BASE_PATH . DIRECTORY_SEPARATOR . 'views';

$config = ArrayHelper::merge(
    require_once BASE_PATH . 'config/web.php',
    require_once BASE_PATH . 'config/_web-local.php',
);

$app = new App($config);

$app->router->mount('/card', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/', 'SiteController@actionCard');
});

$app->router->mount('/site', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/index', 'SiteController@actionIndex');
});

$app->router->mount('/auth', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/login', 'AuthController@actionLogin');

    $app->router->addRoute(Router::ROUTER_POST, '/login', 'AuthController@actionLogin');
    $app->router->addRoute(Router::ROUTER_POST, '/logout', 'AuthController@actionLogout');
});

$app->router->mount('/eligibility', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/', 'EligibilityController@actionIndex');
    $app->router->addRoute(Router::ROUTER_GET, '/full-year', 'EligibilityController@actionFullYear');
    $app->router->addRoute(Router::ROUTER_GET, '/print', 'EligibilityController@actionLoadPrint');
    $app->router->addRoute(Router::ROUTER_GET, '/print/{token:string}', 'EligibilityController@actionPrint');

    $app->router->addRoute(Router::ROUTER_POST, '/search', 'EligibilityController@actionSearch');
    $app->router->addRoute(Router::ROUTER_POST, '/search/full-year', 'EligibilityController@actionSearchFullYear');
});

$app->router->mount('/procedure-lookup', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/', 'ProcedureLookupController@actionProcedureLookup');

    $app->router->addRoute(Router::ROUTER_POST, '/', 'ProcedureLookupController@actionProcedureLookup');
});

$app->router->mount('/procedure', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/', 'ProcedureController@actionIndex');
    $app->router->addRoute(Router::ROUTER_GET, '/create', 'ProcedureController@actionCreate');
    $app->router->addRoute(Router::ROUTER_GET, '/{id:int}/update', 'ProcedureController@actionUpdate');

    $app->router->addRoute(Router::ROUTER_POST, '/create', 'ProcedureController@actionCreate');
    $app->router->addRoute(Router::ROUTER_POST, '/{id:int}/update', 'ProcedureController@actionUpdate');

    $app->router->addRoute(Router::ROUTER_DELETE, '/{id:int}', 'ProcedureController@actionDelete');
});

$app->router->mount('/user', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/', 'UserController@actionIndex');
    $app->router->addRoute(Router::ROUTER_GET, '/create', 'UserController@actionCreate');
    $app->router->addRoute(Router::ROUTER_GET, '/{id:uuid}/update', 'UserController@actionUpdate');

    $app->router->addRoute(Router::ROUTER_POST, '/create', 'UserController@actionCreate');
    $app->router->addRoute(Router::ROUTER_POST, '/{id:uuid}/update', 'UserController@actionUpdate');

    $app->router->addRoute(Router::ROUTER_DELETE, '/{id:uuid}', 'UserController@actionDelete');
});

$app->router->addBeforeRoutes(Router::ROUTER_OPTIONS, [
    ['route' => '/.*', 'action' => function () {
        http_response_code(204);
        exit;
    }],
]);

$app->router->addBeforeRoute(Router::ROUTER_GET, '/.*', function () {
    header("X-Powered-By: jcarr/medquery");

    header('X-Frame-Options: DENY');
});

$app->router->addBeforeRoute(Router::ROUTER_POST, '/.*', function () {
    header("X-Powered-By: jcarr/medquery");

    header('X-Frame-Options: DENY');
});

$app->run();