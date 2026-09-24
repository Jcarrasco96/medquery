<?php

declare(strict_types=1);

namespace app\core;

use app\core\services\ExceptionHandler;
use app\core\services\Logger;
use app\core\services\Request;
use app\core\services\Router;
use app\core\services\Session;
use app\models\User;
use Exception;
use JetBrains\PhpStorm\NoReturn;

final class App
{

    /**
     * @var array<string, string>|array<string, array>
     */
    public static array $config = [
        'name' => 'Tracker',
        'version' => '0.1',
        'timezone' => 'America/New_York',
    ];

    public Router $router;

    public static Request $request;

    public static Session $session;
    public static Logger $logger;

    public static ?User $user = null;

    /**
     * @param array<string, string>|array<string, array> $config
     */
    public function __construct(array $config = [])
    {
        defined('APP_ENV') or define('APP_ENV', 'prod');

        define('APP_ROOT', getcwd() . DIRECTORY_SEPARATOR);
        define('APP_RUNTIME', APP_ROOT . 'runtime' . DIRECTORY_SEPARATOR);
        define('APP_LOGS_FOLDER', APP_RUNTIME . 'logs' . DIRECTORY_SEPARATOR);
        define('APP_RATE_LIMIT_FOLDER', APP_RUNTIME . 'rate_limit' . DIRECTORY_SEPARATOR);

        if (!is_dir(APP_LOGS_FOLDER)) {
            mkdir(APP_LOGS_FOLDER, 0755, true);
        }
        if (!is_dir(APP_RATE_LIMIT_FOLDER)) {
            mkdir(APP_RATE_LIMIT_FOLDER, 0755, true);
        }

        if (APP_ENV === 'dev') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('error_log', APP_LOGS_FOLDER . 'error_' . date('Ymd') . '.log');
        }

        self::$config = array_merge(self::$config, $config);
        self::$logger = new Logger(APP_LOGS_FOLDER . 'app.log');
        self::$request = new Request();
        self::$session = Session::getInstance();

        ExceptionHandler::register();

        date_default_timezone_set(self::$config['timezone']);

        if (self::$session->_id()) {
            self::$user = User::findById(self::$session->_id());
        }

        $this->router = new Router();
    }

    /**
     * @throws Exception
     */
    #[NoReturn]
    public function run(): void
    {
        $this->router->dispatch();
    }

}