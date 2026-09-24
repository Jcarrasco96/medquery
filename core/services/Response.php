<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\helpers\Url;
use JetBrains\PhpStorm\NoReturn;

final class Response
{

    private array $headers = [];

    public function __construct(private readonly mixed $content, private readonly int $statusCode = 200) {

    }

    public function header(string $name, string $value): Response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }

    public static function json(mixed $data, int $statusCode = 200): Response
    {
        $response = new self(json_encode($data), $statusCode);
        $response->header('Content-Type', 'application/json');
        return $response;
    }

    #[NoReturn]
    public static function redirect(string $url, array $params = [], int $code = 302): void
    {
        if (!empty($params)) {
            $separator = (!str_contains($url, '?')) ? '?' : '&';
            $url .= $separator . http_build_query($params);
        }

        header("Location: " . Url::to($url), true, $code);
        exit;
    }

}