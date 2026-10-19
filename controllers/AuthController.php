<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\ServerErrorHttpException;
use app\core\helpers\Url;
use app\core\Permission;
use app\core\services\Response;
use app\models\LoginForm;
use app\utils\MyEasyAjax;
use JetBrains\PhpStorm\NoReturn;

final class AuthController extends Controller
{

    private const int MAX_LOGIN_ATTEMPTS = 3;
    private const int LOCKOUT_TIME = 30;

    /**
     * @throws ServerErrorHttpException
     */
    #[Permission(['?'])]
    public function actionLogin(): string|array
    {
        $model = new LoginForm();

        if (App::$request->isPost() && MyEasyAjax::isSubmit()) {
            $model = LoginForm::fromArray(App::$request->post());

            $csrfToken = App::$request->post('_csrf_token', '');

            if (empty($csrfToken) || !App::$session->checkCSRF($csrfToken)) {
                return MyEasyAjax::instance()
                    ->notifyDanger('CSRF token is invalid. Reload page.')
                    ->payload();
            }

            if (isset(App::$session->login_attempts) && App::$session->login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
                if (isset(App::$session->last_attempt_time)) {
                    $timeSinceLastAttempt = time() - App::$session->last_attempt_time;
                    if ($timeSinceLastAttempt < self::LOCKOUT_TIME) {
                        $model->addError('password', 'Too many failed attempts. Please try again later.');

                        return MyEasyAjax::instance()
                            ->contentReplace([
                                '#content-form' => $this->renderPartial('_form', ['model' => $model])
                            ])
                            ->payload();
                    } else {
                        App::$session->login_attempts = 0;
                    }
                }
            }

            if ($model->validate() && $model->login()) {
                $redirect = urldecode($_GET['redirect'] ?? 'site/index');
                $redirectUrl = str_contains($redirect, '//') ? 'site/index' : $redirect;

                App::$session->removeCSRF();

                return MyEasyAjax::instance()
                    ->redirect(Url::to($redirectUrl))
                    ->payload();
            }

            return MyEasyAjax::instance()
                ->contentReplace([
                    '#content-form' => $this->renderPartial('_form', ['model' => $model])
                ])
                ->payload();
        }

        App::$session->generateCSRF(true);

        $this->layout = 'guest';
        $this->loadScript('assets/js/my-easy-jax.js');

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    #[NoReturn]
    #[Permission(['@'])]
    public function actionLogout(): void
    {
        App::$session->destroy();
        Response::redirect('auth/login');
    }

}