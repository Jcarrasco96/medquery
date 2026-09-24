<?php

declare(strict_types=1);

namespace app\core;

use app\core\services\Response;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Permission
{

    public function __construct(public array $permissions)
    {
    }

    public function check(): bool
    {
        $specialPermissions = [];

        if (in_array('*', $this->permissions)) {
            $specialPermissions[] = '*';
        }
        if (in_array('@', $this->permissions)) {
            $specialPermissions[] = '@';
        }
        if (in_array('?', $this->permissions)) {
            $specialPermissions[] = '?';
        }

        foreach ($specialPermissions as $permission) {
            switch ($permission) {
                case '*':
                    return true;

                case '?':
                    if (App::$session->isGuest()) {
                        return true;
                    }
                    break;

                case '@':
                    if (App::$session->isAuthenticated()) {
                        return true;
                    }

                    $path = App::$request->getPath();

                    if ($path === '/site/index') {
                        Response::redirect('auth/login');
                    }

                    Response::redirect('auth/login', ['redirect' => urlencode($path)]);
            }
        }

        return false;
    }

}