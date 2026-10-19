<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Controller;
use app\core\Permission;
use Exception;

final class SiteController extends Controller
{

    /**
     * @throws Exception
     */
    #[Permission(['@'])]
    public function actionIndex(): string
    {
        return $this->render('index');
    }

}