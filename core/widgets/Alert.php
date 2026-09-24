<?php

declare(strict_types=1);

namespace app\core\widgets;

class Alert
{

    private static array $alert = [
        'danger' => [
            'class' => 'danger',
            'icon' => 'bi bi-x-lg',
        ],
        'success' => [
            'class' => 'success',
            'icon' => 'bi bi-check-lg',
        ],
        'info' => [
            'class' => 'info',
            'icon' => 'bi bi-info-circle',
        ],
        'warning' => [
            'class' => 'warning',
            'icon' => 'bi bi-exclamation-triangle',
        ],
    ];

    public static function run(): string
    {
        $js = "<script>";

        $alerts = $_SESSION['flash'] ?? [];

        unset($_SESSION['flash']);

        foreach ($alerts as $count => $flash) {
            if (!array_key_exists($flash['type'], self::$alert)) {
                continue;
            }

            $jso = 'notify("' . $flash['msg'] . '", "' . self::$alert[$flash['type']]['class'] . '", "' . self::$alert[$flash['type']]['icon'] . '");';

            $js .= $count > 0 ? 'setTimeout(function () {' . $jso . '}, ' . ($count * 500) . ');' : $jso;
        }

        return $js . "</script>";
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = [
            'type' => $type,
            'msg' => $message,
        ];
    }

}