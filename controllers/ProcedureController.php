<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\ForbiddenHttpException;
use app\core\Permission;
use app\core\services\Pagination;
use app\models\Procedure;
use app\utils\MyEasyAjax;

final class ProcedureController extends Controller
{

    /**
     * @throws ForbiddenHttpException
     */
    #[Permission(['@'])]
    public function actionIndex(): string
    {
        if (!App::$session->isAdmin) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        App::$session->page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        App::$session->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        App::$session->order = isset($_GET['order']) ? (string)$_GET['order'] : 'procedure:asc';

        list($procedures, $pagination) = $this->contentProcedures();

        $this->loadScript('assets/js/my-easy-jax.js');

        return $this->render('index', [
            'procedures' => $procedures,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     */
    #[Permission(['@'])]
    public function actionCreate(): array
    {
        if (!App::$session->isAdmin) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        $model = new Procedure();
        $model->scenario = Procedure::SCENARIO_CREATE;

        if (App::$request->isPost() && MyEasyAjax::isSubmit()) {
            $csrfToken = App::$request->post('_csrf_token', '');

            if (empty($csrfToken) || !App::$session->checkCSRF($csrfToken)) {
                return MyEasyAjax::instance()
                    ->notifyDanger('CSRF token is invalid.')
                    ->payload();
            }

            $model->load(App::$request->post());

            if ($model->create()) {
                list($procedures, $pagination) = $this->contentProcedures();

                return MyEasyAjax::instance()
                    ->notifySuccess('Procedure created successfully.')
                    ->contentReplace([
                        "#table-procedures" => $this->renderPartial('_item', [
                            'procedures' => $procedures,
                            'pagination' => $pagination,
                        ]),
                    ])
                    ->modalClose()
                    ->payload();
            }
        }

        App::$session->generateCSRF(true);

        return MyEasyAjax::instance()
            ->modal('Create procedure', $this->renderPartial('_form', ['model' => $model]))
            ->payload();
    }

    /**
     * @throws ForbiddenHttpException
     */
    #[Permission(['@'])]
    public function actionUpdate(string $id): string|array
    {
        if (!App::$session->isAdmin) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        $model = Procedure::findById($id);
        $model->scenario = 'UPDATE';

        if (App::$request->isPost() && MyEasyAjax::isSubmit()) {
            $csrfToken = App::$request->post('_csrf_token', '');

            if (empty($csrfToken) || !App::$session->checkCSRF($csrfToken)) {
                return MyEasyAjax::instance()
                    ->notifyDanger('CSRF token is invalid.')
                    ->payload();
            }

            $model->load(App::$request->post());

            if ($model->update()) {
                list($procedures, $pagination) = $this->contentProcedures();

                return MyEasyAjax::instance()
                    ->notifySuccess('Procedure updated successfully.')
                    ->contentReplace([
                        "#table-procedures" => $this->renderPartial('_item', [
                            'procedures' => $procedures,
                            'pagination' => $pagination,
                        ]),
                    ])
                    ->modalClose()
                    ->payload();
            }
        }

        App::$session->generateCSRF(true);

        return MyEasyAjax::instance()
            ->modal('Update procedure', $this->renderPartial('_form', [
                'model' => $model,
            ]))
            ->payload();
    }

    #[Permission(['@'])]
    public function actionDelete(int $id): array
    {
        if (!App::$session->isAdmin) {
            return MyEasyAjax::instance()
                ->notifyDanger('You are not allowed to perform this action.')
                ->payload();
        }

        $model = Procedure::findById($id);

        if ($model->delete()) {
            list($procedures, $pagination) = $this->contentProcedures();

            return MyEasyAjax::instance()
//                ->contentRemove("td_$id")
                ->contentReplace([
                    "#table-procedures" => $this->renderPartial('_item', [
                        'procedures' => $procedures,
                        'pagination' => $pagination,
                    ]),
                ])
                ->notifySuccess('Procedure deleted successfully.')
                ->payload();
        }

        return MyEasyAjax::instance()
            ->notifyDanger('Procedure not found.')
            ->payload();
    }

    private function contentProcedures(): array
    {
        $page = max(1, App::$session->page ?? 1);
        $limit = max(1, App::$session->limit ?? 10);
        $order = App::$session->order ?? 'procedure:asc';

        list($procedures, $count) = Procedure::findRange($page, $limit, $order);

        $pagination = new Pagination($page - 1, $limit, $count, $order);

        if ($page > $pagination->getPageCount() && $page > 1) {
            $page = $pagination->getPageCount();

            App::$session->page = $page;

            list($procedures, $count) = Procedure::findRange($page, $limit, $order);

            $pagination = new Pagination($page - 1, $limit, $count, $order);
        }

        return [$procedures, $pagination];
    }

}