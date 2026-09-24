<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\Permission;
use app\core\services\Response;
use app\models\LoginForm;
use Exception;
use JetBrains\PhpStorm\NoReturn;

final class AuthController extends Controller
{

    private const int MAX_LOGIN_ATTEMPTS = 3;
    private const int LOCKOUT_TIME = 30;

    /**
     * @throws Exception
     */
    #[Permission(['?'])]
    public function actionLogin(): string
    {
        $this->layout = 'guest';

        $model = new LoginForm();

        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            if (isset($_SESSION['last_attempt_time'])) {
                $timeSinceLastAttempt = time() - $_SESSION['last_attempt_time'];
                if ($timeSinceLastAttempt < self::LOCKOUT_TIME) {
                    $model->addError('password', 'Too many failed attempts. Please try again later.');

                    return $this->render('login', [
                        'model' => $model,
                    ]);
                } else {
                    $_SESSION['login_attempts'] = 0;
                }
            }
        }

        if (App::$request->isPost()) {
            $model = LoginForm::fromArray(App::$request->post());

            $this->validateCsrf('auth/login');

            if ($model->validate() && $model->login()) {
                $redirect = urldecode($_GET['redirect'] ?? 'site/index');
                $redirectUrl = str_contains($redirect, '//') ? 'site/index' : $redirect;
                Response::redirect($redirectUrl);
            }
        }

        App::$session->generateCSRF(true);

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