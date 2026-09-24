<?php

declare(strict_types=1);

namespace app\core;

use app\core\exceptions\ForbiddenHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\core\exceptions\TooManyRequestsHttpException;
use app\core\services\Renderer;
use app\core\services\Response;
use app\core\widgets\Alert;
use ReflectionException;
use ReflectionMethod;

class Controller
{

    private string $controllerName;

    protected string $layout = 'main';

    protected float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);

        $className = get_class($this);
        $className = basename(str_replace('\\', '/', $className));

        if (str_ends_with($className, 'Controller')) {
            $this->controllerName = strtolower(substr($className, 0, -10));
        } else {
            $this->controllerName = strtolower($className);
        }
    }

    /**
     * @throws TooManyRequestsHttpException
     * @throws ForbiddenHttpException
     * @throws ReflectionException
     */
    protected function beforeAction(string $methodName): void
    {
        $method = new ReflectionMethod($this, $methodName);

        $attributes = $method->getAttributes();

        $allowAction = empty($method->getAttributes(Permission::class));

        $allowedAttributes = [
            RateLimitChecker::class,
            Permission::class,
        ];

        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getName(), $allowedAttributes)) {
                continue;
            }

            $instance = $attribute->newInstance();

            if ($instance instanceof RateLimitChecker) {
                $instance->check("{$this->controllerName}_$method->name");
            } elseif ($instance instanceof Permission) {
                if ($allowAction) {
                    continue;
                }

                $allowAction = $instance->check();
            }
        }

        if (!$allowAction) {
            throw new ForbiddenHttpException('You do not have permission to access this page.');
        }

        if (App::$session->isAuthenticated()) {
            // reset roles
            $selectedRole = App::$session->getSelectedRole();
            $roles = App::$session->roles();

            if (empty($roles)) {
                App::$session->clearSelectedRole();
            } elseif (!App::$session->can($selectedRole)) {
                $selectedRole = array_shift($roles);
                App::$session->setSelectedRole($selectedRole);
            }
        }
    }

    /**
     * @throws ServerErrorHttpException
     */
    protected function render(string $view, array $data = []): string
    {
        if (!isset($data['execTime'])) {
            $data['execTime'] = number_format(microtime(true) - $this->startTime, 5);
        }

        if (empty($data['pageTitle'])) {
            $data['pageTitle'] = ucfirst($view);
        }

        $data['styles'] = $this->styles;
        $data['scripts'] = $this->scripts;
        $data['headScripts'] = $this->headScripts;

        $renderer = new Renderer($this->controllerName);

        return $renderer->render($view, $data, $this->layout);
    }

    /**
     * @throws ServerErrorHttpException
     */
    protected function renderPartial(string $view, array $data = []): string
    {
        if (!isset($data['execTime'])) {
            $data['execTime'] = number_format(microtime(true) - $this->startTime, 5);
        }

        $renderer = new Renderer($this->controllerName);

        return $renderer->renderPartial($view, $data);
    }

    protected function asJson($data, $statusCode = 200): Response
    {
        if (!isset($data['execTime'])) {
            $data['execTime'] = number_format(microtime(true) - $this->startTime, 5);
        }

        return Response::json($data, $statusCode);
    }

    /**
     * @throws ReflectionException
     * @throws TooManyRequestsHttpException
     * @throws ForbiddenHttpException
     */
    public function createAction($methodName, $params = []): string|array|Response
    {
        App::$request->setRouteParams($params);

        App::$session->startSession();

        $this->beforeAction($methodName);

        return call_user_func_array([$this, $methodName], $params);
    }

    public array $styles = [];
    public array $scripts = [];
    public array $headScripts = [];

    protected function loadStyle(string $cssFile, array $attributes = []): void
    {
        $this->styles[] = [
            'file' => $cssFile,
            'attributes' => $attributes
        ];
    }

    protected function loadScript(string $jsFile, array $attributes = [], bool $inHead = false): void
    {
        $scriptData = [
            'file' => $jsFile,
            'attributes' => $attributes
        ];

        if ($inHead) {
            $this->headScripts[] = $scriptData;
        } else {
            $this->scripts[] = $scriptData;
        }
    }

    public function validateCsrf(string $redirect = 'site/index', array $params = []): void
    {
        $csrfToken = App::$request->post('_csrf_token', '');

        if (empty($csrfToken) || !App::$session->checkCSRF($csrfToken)) {
            Alert::flash('danger', 'Invalid CSRF token.');
            Response::redirect($redirect, $params);
        }
    }

}