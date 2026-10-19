<?php

declare(strict_types=1);

namespace app\utils\debugger;

final class MyDebugger
{

    private const string GET = "GET";
    private const string POST = "POST";
    private const string COOKIE = "COOKIE";
    private const string FILES = "FILES";
    private const string SERVER = "SERVER";
    private const string ENV = "ENV";
    private const string SESSION = "SESSION";
    private const string PHP = "PHP";

    private const string HIDDEN_FIELD = '[REDACT]';

    private static bool $showCriticalFields = false;
    private static bool $useBootstrapIcons = true;

    private static array $panels = [
        self::GET,
        self::POST,
        self::COOKIE,
//        self::FILES,
        self::SERVER,
//        self::ENV,
        self::SESSION,
//        self::PHP,
    ];

    /** @var DebuggerCollectorInterface[] */
    private static array $collectors = [];

    public static function run(array $options = [], array $load = []): string
    {
        if (!self::isAllowed()) {
            return '';
        }

        if (isset($options['showCriticalFields']) && $options['showCriticalFields']) {
            self::$showCriticalFields = true;
        }
        if (isset($options['useBootstrapIcons']) && $options['useBootstrapIcons']) {
            self::$useBootstrapIcons = true;
        }

        foreach ($load as $collector) {
            if ($collector instanceof DebuggerCollectorInterface) {
                self::$collectors[$collector->getName()] = $collector;
            }
        }

        $html = '<div id="my-debugger">';
        $html .= '<div class="my-debugger-header">';
        $html .= '<strong>MyDebugger</strong>';
        $html .= '<span>Development tools</span>';
        if (self::$useBootstrapIcons) {
            $html .= '<button type="button" id="my-debugger-toggle"><i class="bi bi-dash-lg"></i></button>';
        } else {
            $html .= '<button type="button" id="my-debugger-toggle">−</button>';
        }

        $html .= '</div>';

        $html .= '<div class="my-debugger-content">';
        $html .= '<nav class="my-debugger-tabs">';

        foreach (self::$panels as $key) {
            $html .= sprintf('<button type="button" data-panel="%s">%s</button>', self::escape($key), self::escape($key));
        }

        if (!empty(self::$collectors)) {
            $html .= '<span style="width: 5px;"></span>';
            foreach (self::$collectors as $name => $collector) {
                $html .= sprintf('<button type="button" data-panel="%s">%s</button>', self::escape($name), self::escape($name));
            }
        }

        $html .= '</nav>';

        foreach (self::$panels as $key) {
            $html .= sprintf('<section class="my-debugger-panel" id="panel-%s">', self::escape($key));
            $html .= self::renderArray(self::getData($key));
            $html .= '</section>';
        }

        if (!empty(self::$collectors)) {
            foreach (self::$collectors as $name => $collector) {
                $html .= sprintf('<section class="my-debugger-panel" id="panel-%s">', self::escape($name));
                $html .= self::renderArray(self::getData($name));
                $html .= '</section>';
            }
        }

        $html .= '</div></div>';

        if (self::$useBootstrapIcons) {
            $html .= '<button type="button" id="my-debugger-open"><i class="bi bi-bug-fill"></i></button>';
        } else {
            $html .= '<button type="button" id="my-debugger-open">Debugger</button>';
        }

        $html .= '<link rel="stylesheet" href="/utils/debugger/assets/debugger.css">';
        $html .= '<script src="/utils/debugger/assets/debugger.js" defer></script>';

        return $html;
    }

    private static function isAllowed(): bool
    {
        return defined('APP_ENV') && APP_ENV === 'dev';
    }

    private static function getData(string $key): array
    {
        if (isset(self::$collectors[$key])) {
            return self::redact(self::$collectors[$key]->collect());
        }

        return match ($key) {
            self::GET => $_GET,
            self::POST => self::redact($_POST),
            self::COOKIE => self::redact($_COOKIE),
            self::FILES => $_FILES,
            self::SERVER => self::filterServer($_SERVER),
            self::ENV => self::redact($_ENV),
            self::SESSION => self::redact($_SESSION ?? []),
            self::PHP => self::redact(self::phpInfo()),
            default => [],
        };
    }

    private static function filterServer(array $server): array
    {
        unset(
            $server['PHP_AUTH_PW'],
            $server['HTTP_AUTHORIZATION']
        );

        return $server;
    }

    private static function redact(array $data): array
    {
        $sensitive = [
            'password',
            'passwd',
            'token',
            'secret',
            'authorization',
            'api_key',
            'apikey',
            'credential',
            'csrf',
        ];

        foreach ($data as $key => $value) {
            if (!self::$showCriticalFields) {
                $normalized = strtolower((string)$key);

                foreach ($sensitive as $word) {
                    if (str_contains($normalized, $word)) {
                        $data[$key] = self::HIDDEN_FIELD;
                        continue 2;
                    }
                }
            }

            if (is_array($value)) {
                $data[$key] = self::redact($value);
            }
        }

        return $data;
    }

    private static function renderArray(array $data): string
    {
        if (empty($data)) {
            return '<code>No data.</code>';
        }

        $html = '<table class="my-debugger-table"><tbody>';

        foreach ($data as $key => $value) {
            $html .= '<tr><td>' . htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '</td><td>';

            if (is_array($value)) {
                $html .= self::renderArray($value);
            } elseif (is_bool($value)) {
                $html .= $value ? 'true' : 'false';
            } elseif ($value === null) {
                $html .= '<em>null</em>';
            } elseif (is_object($value)) {
                $html .= htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR), ENT_QUOTES, 'UTF-8');
            } elseif ($value == self::HIDDEN_FIELD) {
                $html .= '<code>Hidden for security</code>';
            } else {
                $html .= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }

            $html .= '</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function phpInfo(): array
    {
        ob_start();

        phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES);

        $info_arr = [];

        $info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));

        $cat = 'General';

        foreach ($info_lines as $line) {
            if (preg_match("~<h2>(.*)</h2>~", $line, $title)) {
                $cat = $title[1];
            }

            if (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                $info_arr[$cat][$val[1]] = $val[2];
            } elseif (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                $info_arr[$cat][$val[1]] = ["local" => $val[2], "master" => $val[3]];
            }
        }

        return $info_arr;
    }

}