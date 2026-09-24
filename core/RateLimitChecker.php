<?php

declare(strict_types=1);

namespace app\core;

use app\core\exceptions\TooManyRequestsHttpException;
use app\core\services\Request;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class RateLimitChecker
{

    public function __construct(public int $limit = 5, public int $seconds = 60)
    {
    }

    /**
     * @throws TooManyRequestsHttpException
     */
    public function check(string $key): void {
        $clientId = hash('sha256', Request::getIp());

        $path = APP_RATE_LIMIT_FOLDER . "rate_limit_$key.$clientId.json";

        $now = time();

        if (!file_exists($path)) {
            file_put_contents($path, json_encode(['count' => 1, 'timestamp' => time()]));
            self::setHeaders($this->limit, $this->limit - 1, $now + $this->seconds);
            return;
        }

        $data = json_decode(file_get_contents($path), true);

        if ($now - $data['timestamp'] > $this->seconds) {
            file_put_contents($path, json_encode(['count' => 1, 'timestamp' => $now]));
            self::setHeaders($this->limit, $this->limit - 1, $now + $this->seconds);
            return;
        }

        if ($data['count'] >= $this->limit) {
            header('Retry-After: ' . $this->seconds);
            self::setHeaders($this->limit, 0, $data['timestamp'] + $this->seconds);
            throw new TooManyRequestsHttpException('Rate limit exceeded');
        }

        $data['count']++;
        file_put_contents($path, json_encode($data));
        self::setHeaders($this->limit, $this->limit - $data['count'], $data['timestamp'] + $this->seconds);
    }

    private function setHeaders(int $limit, int $remaining, int $reset): void
    {
        header('X-RateLimitChecker-Limit: ' . $limit);
        header('X-RateLimitChecker-Remaining: ' . $remaining);
        header('X-RateLimitChecker-Reset: ' . $reset);
    }

}