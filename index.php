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
//    require_once BASE_PATH . 'config/web.local.php',
    require_once BASE_PATH . 'config/web.local-laptop.php',
);

$app = new App($config);

$app->router->mount('/site', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/index', 'SiteController@actionIndex');

    $app->router->addRoute(Router::ROUTER_POST, '/select-role/{role}', 'SiteController@actionSelectRole');
});

$app->router->mount('/auth', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/login', 'AuthController@actionLogin');

    $app->router->addRoute(Router::ROUTER_POST, '/login', 'AuthController@actionLogin');
    $app->router->addRoute(Router::ROUTER_POST, '/logout', 'AuthController@actionLogout');
});

$app->router->mount('/eligibility', function () use ($app) {
    $app->router->addRoute(Router::ROUTER_GET, '/', 'EligibilityController@actionIndex');
    $app->router->addRoute(Router::ROUTER_POST, '/search', 'EligibilityController@actionSearch');
    $app->router->addRoute(Router::ROUTER_GET, '/search/{medicaid:string}', 'EligibilityController@actionSearchMedicaid');
});

$app->router->addBeforeRoutes(Router::ROUTER_OPTIONS, [
    ['route' => '/.*', 'action' => function () {
        http_response_code(204);
        exit;
    }],
]);

$app->router->addBeforeRoute(Router::ROUTER_GET, '/.*', function () {
    header("X-Powered-By: jcarr/medquery");
});

$app->run();