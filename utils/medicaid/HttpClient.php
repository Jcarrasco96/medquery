<?php

namespace app\utils\medicaid;

use CurlHandle;
use RuntimeException;

final class HttpClient
{
    private CurlHandle $curl;

    public function __construct(private readonly string $cookieFile)
    {
        $directory = dirname($cookieFile);

        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException("Unable to create cookie directory: $directory");
        }

        $this->curl = curl_init();

        curl_setopt_array($this->curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,

            // Persistent session
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,

            CURLOPT_USERAGENT =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                . 'AppleWebKit/537.36 '
                . '(KHTML, like Gecko) '
                . 'Chrome/140.0 Safari/537.36',

            CURLOPT_ENCODING => '',

            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,

            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml',
            ],

            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    public function get(string $url): string
    {
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPGET => true,
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => null,
        ]);

        return $this->execute();
    }

    public function post(string $url, array $data): string
    {
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);

        return $this->execute();
    }

    public function postReferer(string $url, array $data, ?string $referer = null): string {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml',
        ];

        if ($referer !== null) {
            $headers[] = 'Referer: ' . $referer;
        }

        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        return $this->execute();
    }

    private function execute(): string
    {
        $response = curl_exec($this->curl);

        if ($response === false) {
            throw new RuntimeException(curl_error($this->curl));
        }

        return $response;
    }

    public function info(): array
    {
        return curl_getinfo($this->curl);
    }

    public function url()
    {
        return $this->info()['url'] ?? '';
    }

    public function clearCookies(): void
    {
        curl_setopt($this->curl, CURLOPT_COOKIELIST, 'ALL');

        if (is_file($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }
}