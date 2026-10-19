<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\BadRequestHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\core\helpers\Url;
use app\core\Permission;
use app\models\EligibilityForm;
use app\models\FullEligibilityForm;
use app\utils\JwtService;
use app\utils\medicaid\HttpClient;
use app\utils\medicaid\PageAnalyzer;
use app\utils\MyEasyAjax;
use DateMalformedStringException;
use DateTimeImmutable;

final class EligibilityController extends Controller
{

    /**
     * @throws ServerErrorHttpException
     */
    #[Permission(['@'])]
    public function actionIndex(): string
    {
        $model = new EligibilityForm();

        $this->loadScript('assets/js/my-easy-jax.js');

        App::$session->generateCSRF(true);

        return $this->render('index', ['model' => $model]);
    }

    #[Permission(['@'])]
    public function actionFullYear(): string
    {
        $model = new FullEligibilityForm();

        $this->loadScript('assets/js/my-easy-jax.js');

        App::$session->generateCSRF(true);

        return $this->render('full-year', ['model' => $model]);
    }

    #[Permission(['@'])]
    public function actionSearch(): array
    {
        $model = new EligibilityForm();

        $model->load(App::$request->post());

        if (!$model->validate()) {
            return MyEasyAjax::instance()
                ->contentReplace([
                    '#formContent' => $this->renderPartial('_form', ['model' => $model]),
                    '#eligibilityContent' => '',
                ])
                ->payload();
        }

        $client = new HttpClient('runtime/cookies/session.cookie');

        try {
            $page = PageAnalyzer::eligibilityPage($client);

            $eligibility = $page->postSearch($client, $model->medicaid_id, $model->fromDateUS, $model->toDateUS)->parse();

            $content = $this->renderPartial('_content', [
                'eligibility' => $eligibility,
                'url' => Url::to("eligibility/print", [
                    'medicaid_id' => $model->medicaid_id,
                    'from_date' => $model->from_date,
                    'to_date' => $model->to_date,
                ]),
            ]);
        } catch (BadRequestHttpException|ServerErrorHttpException $e) {
            $content = '<code>' . $e->getMessage() . '</code>';
        }

        return MyEasyAjax::instance()
            ->contentReplace([
                '#formContent' => $this->renderPartial('_form', ['model' => $model,]),
                '#eligibilityContent' => $content,
            ])
            ->payload();
    }

    #[Permission(['@'])]
    public function actionSearchFullYear(): array
    {
        $model = new FullEligibilityForm();

        $model->load(App::$request->post());

        if (!$model->validate()) {
            return MyEasyAjax::instance()
                ->contentReplace([
                    '#formContent' => $this->renderPartial('_form-full-year', ['model' => $model]),
                    '#eligibilityContent' => '',
                ])
                ->payload();
        }

        try {
            list($monthlyResults, $eligibility) = $this->monthlyResults($model->medicaid_id);

            $content = $this->renderPartial('_full-year-content', [
                'medicaid' => $model->medicaid_id,
                'monthlyResults' => $monthlyResults,
                'eligibility' => $eligibility,
            ]);
        } catch (DateMalformedStringException|BadRequestHttpException|ServerErrorHttpException $e) {
            $content = '<code>' . $e->getMessage() . '</code>';
        }

        return MyEasyAjax::instance()
            ->contentReplace([
                '#formContent' => $this->renderPartial('_form-full-year', ['model' => $model]),
                '#eligibilityContent' => $content,
            ])
            ->payload();
    }

    /**
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    #[Permission(['@'])]
    public function actionPrint(string $token): array|string
    {
        $model = new EligibilityForm();

        $tokenDecoded = JwtService::decode($token);

        $model->load($tokenDecoded);

        if (!$model->validate()) {
            return '<code>ERROR!</code>';
        }

        $client = new HttpClient('runtime/cookies/session.cookie');

        $page = PageAnalyzer::eligibilityPage($client);

        list($title, $content) = $page->postSearch($client, $model->medicaid_id, $model->fromDateUS, $model->toDateUS)->eligibilityTable();

        return $this->renderPartial('_print', [
            'title' => $title,
            'content' => $content,
        ]);
    }

    #[Permission(['@'])]
    public function actionLoadPrint(): array
    {
        $medicaid = App::$request->get('medicaid_id');
        $fromDateOriginal = App::$request->get('from_date');
        $toDateOriginal = App::$request->get('to_date');

        $time = time();

        $token = JwtService::encode([
            'iss' => App::$config['name'],
            'iat' => $time,
            'exp' => $time + 3600,

            'medicaid_id' => $medicaid,
            'from_date' => $fromDateOriginal,
            'to_date' => $toDateOriginal,
        ]);

        return MyEasyAjax::instance()
            ->openWindow(Url::to("eligibility/print/$token"))
            ->payload();
    }

    /**
     * @throws DateMalformedStringException
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    private function monthlyResults(string $medicaid_id): array
    {
        $client = new HttpClient('runtime/cookies/session.cookie');

        $page = PageAnalyzer::eligibilityPage($client);

        $today = new DateTimeImmutable('today');
        $minusYear = $today->modify('-1 year');

        $monthlyResults = [];

        for ($i = 12; $i >= 0; $i--) {
            $month = $today->modify("-$i months");

            $fromDate = $month->modify('first day of this month');
            $toDate = $month->modify('last day of this month');

            if ($toDate > $today) {
                $toDate = $today;
            }

            if ($fromDate < $minusYear) {
                $fromDate = $minusYear;
            }

            $from = $fromDate->format('m/d/Y');
            $to = $toDate->format('m/d/Y');

            $eligibility = $page->postSearch($client, $medicaid_id, $from, $to)->parse();

            $monthlyResults[] = [
                'month' => $month->format('F Y'),
                'from' => $from,
                'to' => $to,

                'from_date' => $fromDate->format('Y-m-d'),
                'to_date' => $toDate->format('Y-m-d'),

                'eligibility' => [
                    'status' => $eligibility->hmo['status'],
                    'type' => $eligibility->hmo['type'],
                ],
                'benefit_plans' => $eligibility->listBenefitPlan,
                'managed_care' => $eligibility->listManagedCare,
                'tpl' => $eligibility->listTPL,
            ];
        }

        return [$monthlyResults, $eligibility];
    }

}