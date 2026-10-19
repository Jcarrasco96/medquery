<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\BadRequestHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\core\Permission;
use app\models\Procedure;
use app\models\ProcedureLookup;

final class ProcedureLookupController extends Controller
{

    /**
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    #[Permission(['@'])]
    public function actionProcedureLookup(): string
    {
        $model = new ProcedureLookup();

        $procedures = Procedure::findAll();

        if (App::$request->isPost()) {

            $this->validateCsrf('procedure-lookup');

            $model = ProcedureLookup::fromArray(App::$request->post());

            if (!$model->validate()) {
                return $this->render('procedure-lookup', [
                    'model' => $model,
                    'procedures' => $procedures,
                    'pageTitle' => 'Procedure Lookup'
                ]);
            }

            $model->parseResult();

            return $this->render('procedure-lookup', [
                'model' => $model,
                'procedures' => $procedures,
                'pageTitle' => 'Procedure Lookup'
            ]);
        }

        App::$session->generateCSRF(true);

        return $this->render('procedure-lookup', [
            'model' => $model,
            'procedures' => $procedures,
            'pageTitle' => 'Procedure Lookup'
        ]);
    }

}