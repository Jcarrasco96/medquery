<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\App;
use ErrorException;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Throwable;

final class ExceptionHandler
{

    public static function register(): void
    {
        set_exception_handler([self::class, 'exceptionHandle']);
        set_error_handler([self::class, 'errorHandle']);
    }

    #[NoReturn]
    public static function exceptionHandle(Throwable $throwable): void
    {
        $code = ($throwable->getCode() == 0 || !is_int($throwable->getCode())) ? 401 : $throwable->getCode();

        http_response_code($code);

        if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1' && !headers_sent()) {
            header('HTTP/1.1 503 Service Unavailable');
        }

        $params = [
            'code' => $code,
            'message' => $throwable->getMessage(),
            'trace' => APP_ENV == 'prod' ? 'NO TRACE ALLOWED' : $throwable->getTraceAsString(),
            'controllerName' => 'site',
            'pageTitle' => $code,
        ];

        App::$session->startSession();

        App::$logger->error(self::format($throwable));

        if (self::isJsonRequest()) {
            Response::json($params)->send();
            exit();
        }

        try {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $renderer = new Renderer();

            if (APP_ENV == 'prod') {
                $params['trace'] = '';
            }

            echo $renderer->render('error', $params);
        } catch (Exception) {
            echo self::format($throwable) . PHP_EOL;
        }
    }

    /**
     * @throws ErrorException
     */
    public static function errorHandle(int $severity, string $message, string $file, int $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    private static function isJsonRequest(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))
            || isset($_GET['json']);
    }

    private static function format(Throwable $e): string
    {
        return sprintf("%s in %s:%d\nStack trace:\n%s\n",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }

}