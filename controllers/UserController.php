<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\ForbiddenHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\core\Permission;
use app\core\services\Response;
use app\core\widgets\Alert;
use app\models\User;
use app\utils\MyEasyAjax;
use Random\RandomException;

final class UserController extends Controller
{

    /**
     * @throws ServerErrorHttpException
     * @throws ForbiddenHttpException
     */
    #[Permission(['@'])]
    public function actionIndex(): string|Response
    {
        if (!App::$session->isAdmin) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        $this->loadScript('assets/js/my-easy-jax.js');

        $users = User::findAll();

        return $this->render('index', [
            'users' => $users,
        ]);
    }

    /**
     * @throws ServerErrorHttpException
     * @throws ForbiddenHttpException
     * @throws RandomException
     */
    #[Permission(['@'])]
    public function actionCreate(): string|array
    {
        if (!App::$session->isAdmin) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        $model = new User();

        if (App::$request->isPost() && MyEasyAjax::isSubmit()) {
            $csrfToken = App::$request->post('_csrf_token', '');

            if (empty($csrfToken) || !App::$session->checkCSRF($csrfToken)) {
                return MyEasyAjax::instance()
                    ->notifyDanger('CSRF token is invalid.')
                    ->payload();
            }

            $model = User::fromArray(App::$request->post());

            if ($model->validate() && $model->create()) {
                App::$session->removeCSRF();

                Alert::flash('success', 'User created successfully.');

                return MyEasyAjax::instance()
                    ->reloadPage()
                    ->payload();
            }
        }

        App::$session->generateCSRF(true);

        return MyEasyAjax::instance()
            ->modal('Create user', $this->renderPartial('create', ['model' => $model]))
            ->payload();
    }

    /**
     * @throws ServerErrorHttpException
     * @throws ForbiddenHttpException
     */
    #[Permission(['@'])]
    public function actionUpdate(string $id): string|array
    {
        if (!App::$session->isAdmin) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        $model = User::findById($id);

        unset($model->password);
        unset($model->auth_key);

        if (App::$request->isPost() && MyEasyAjax::isSubmit()) {
            $csrfToken = App::$request->post('_csrf_token', '');

            if (empty($csrfToken) || !App::$session->checkCSRF($csrfToken)) {
                return MyEasyAjax::instance()
                    ->notifyDanger('CSRF token is invalid.')
                    ->payload();
            }

            $model = User::fromArray(App::$request->post());

            $model->validate();

            if ($model->getError('password')) {
                $model->clearError('password');
            }

            $isValid = empty($model->getErrors());

            if ($isValid) {
//                $model->create();
                Alert::flash('success', 'User created successfully.');

                return MyEasyAjax::instance()
                    ->reloadPage()
                    ->payload();
            }
        }

        App::$session->generateCSRF(true);

        return MyEasyAjax::instance()
            ->modal('Update client', $this->renderPartial('update', ['model' => $model]))
            ->payload();
    }

    #[Permission(['@'])]
    public function actionDelete(string $id): array
    {
        if (!App::$session->isAdmin) {
            return MyEasyAjax::instance()
                ->notifyDanger('You are not allowed to perform this action.')
                ->payload();
        }

        $sessionId = App::$session->_id;

        if ($sessionId == $id) {
            return MyEasyAjax::instance()
                ->notifyDanger('You are not allowed to perform this action.')
                ->payload();
        }

        $deleted = User::delete($id);

        if ($deleted) {
            return MyEasyAjax::instance()
                ->contentRemove("td_$id")
                ->notifySuccess('User deleted successfully.')
                ->payload();
        }

        return MyEasyAjax::instance()
            ->notifyDanger('User not found.')
            ->payload();
    }

}