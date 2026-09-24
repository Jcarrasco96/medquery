<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\Permission;
use app\core\services\Response;
use Exception;

final class SiteController extends Controller
{

    /**
     * @throws Exception
     */
    #[Permission(['@'])]
    public function actionIndex(): string|Response
    {
        $this->loadScript('/assets/js/views/index.js');

        return $this->render('index');
    }

    #[Permission(['@'])]
    public function actionSelectRole(string $role): Response
    {
        App::$session->setSelectedRole($role);

        return $this->asJson(['status' => 200, 'message' => $role]);
    }

}