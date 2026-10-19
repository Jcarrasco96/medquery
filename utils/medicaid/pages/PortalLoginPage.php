<?php

declare(strict_types=1);

namespace app\utils\medicaid\pages;

use app\utils\medicaid\GenericPage;

final class PortalLoginPage extends GenericPage
{

    public function login(string $username, string $password): array
    {
        $fields['UserName'] = $username;
        $fields['Password'] = $password;

        return $fields;
    }

}