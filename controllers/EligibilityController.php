<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\exceptions\BadRequestHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\utils\medicaid\EligibilityResultParser;
use app\utils\medicaid\GenericForm;
use app\utils\medicaid\HttpClient;
use app\utils\medicaid\PageAnalysis;
use app\utils\medicaid\PageAnalyzer;
use app\utils\medicaid\PageState;
use DateTime;
use DateTimeImmutable;
use RuntimeException;

class EligibilityController extends Controller
{

    /**
     * @throws ServerErrorHttpException
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function actionSearch(): string
    {
        $client = new HttpClient('runtime/cookies/session.cookie');

        $url = 'https://portal.flmmis.com/FLPortal/Eligibility/tabId/68/Default.aspx';

        $html = $client->get($url);

        $attempts = 0;
        $maxAttempts = 3;

        $analyzer = new PageAnalyzer($html);
        $analysis = $analyzer->analyze($client->url());

        while ($analysis->state != PageState::ELIGIBILITY) {

            App::$logger->notice('PAGE STATUS: ' . $analysis->state->value);

            switch ($analysis->state) {
                case PageState::INCORRECT_CREDENTIALS:
                    throw new BadRequestHttpException('Incorrect user ID or password.');

                case PageState::LOGIN:
                    $analysis = $this->loginFLMedicaid($client, $analysis->form);

                    break;

                case PageState::SAML_POST:
                    $analysis = $this->processSaml($client, $analysis->form);

                    break;

                case PageState::SESSION_EXPIRED:
                    $client->clearCookies();

                    throw new ServerErrorHttpException('Session expired. Cookie deleted. Reload page.');

                default:
                    $html = $client->get($url);

                    $analyzer = new PageAnalyzer($html);
                    $analysis = $analyzer->analyze($client->url());

                    break;
            }

            $attempts++;

            if ($attempts > $maxAttempts) {
                throw new ServerErrorHttpException('Too many attempts.');
            }
        }

        $eligibilityForm = new GenericForm($html);

        $medicaid = App::$request->post('medicaid_id');
        $fromDateOriginal = App::$request->post('from_date');
        $toDateOriginal = App::$request->post('to_date');

        $fDate = DateTime::createFromFormat('Y-m-d', $fromDateOriginal);
        $fromDate = $fDate->format('m/d/Y');

        if ($toDateOriginal) {
            $tDate = DateTime::createFromFormat('Y-m-d', $toDateOriginal);
            $toDate = $tDate->format('m/d/Y');
        } else {
            $toDate = null;
        }

        $fields = $eligibilityForm->eligibilityWithSearchCriteria($medicaid, $fromDate, $toDate);

        $result = $client->postReferer(PageAnalyzer::absoluteUrl($eligibilityForm->action, $url), $fields, $url);

        $eligibility = new EligibilityResultParser($result);

        //file_put_contents('runtime/eligibility.html', $result);

        return $this->render('result', [
            'eligibility' => $eligibility,

            'medicaid' => $medicaid,
            'fromDate' => $fromDateOriginal,
            'toDate' => $toDateOriginal,
        ]);
    }

    public static function realDelete(string $path): bool
    {
        if (is_link($path) || is_file($path)) {
            return unlink($path);
        }

        if (!is_dir($path)) {
            return false;
        }

        $objects = scandir($path);
        $ok = true;
        foreach ($objects as $file) {
            if (in_array($file, ['.', '..', 'System Volume Information', '$RECYCLE.BIN'])) continue;
            $ok = $ok && self::realDelete($path . DIRECTORY_SEPARATOR . $file);
        }
        return $ok && rmdir($path);
    }

    /**
     * @throws ServerErrorHttpException
     * @throws \DateMalformedStringException
     */
    public function actionSearchMedicaid(string $medicaid): string
    {
        $today = new DateTimeImmutable('today');

        $monthlyResults = [];

        // load http
        $client = new HttpClient('runtime/cookies/session.cookie');

        $url = 'https://portal.flmmis.com/FLPortal/Eligibility/tabId/68/Default.aspx';

        $html = $client->get($url);

        $analyzer = new PageAnalyzer($html);

        $analysis = $analyzer->analyze($client->url());

        if ($analysis->state == PageState::LOGIN) {
            echo "LOGIN detected." . PHP_EOL;

            $form = new GenericForm($html);

            $fields = $form->login(App::$config['medicaid']['username'], App::$config['medicaid']['password']);

            $html = $client->postReferer("https://sso.flmmis.com" . $form->action, $fields, $client->url());

            $analysis = $analyzer->analyze($client->url());
        }

        if ($analysis->state == PageState::SAML_POST) {
            echo "SAML POST detected." . PHP_EOL;

            $samlUrl = PageAnalyzer::absoluteUrl($analysis->form->action, $client->url());

            $html = $client->postReferer($samlUrl, $analysis->form->fields, $client->url());

            $analysis = $analyzer->analyze($client->url());
        }

        if ($analysis->state == PageState::SESSION_EXPIRED) {
            if (@unlink('runtime/cookies/session.cookie')) {
                die('Session expired. Cookie deleted. Reload page.');
            }

            die('Session expired. Cookie not deleted. Reload page.');
        }

        if ($analysis->state == PageState::CAPTCHA) {
            file_put_contents('runtime/captcha.html', $html);
            die('Captcha.');
        }

        if ($analysis->state != PageState::ELIGIBILITY) {
            file_put_contents('runtime/no-eligibility.html', $html);

            echo "STATUS: " . $analysis->state->value . " detected." . PHP_EOL;
            die();
//            return [];
        }

        for ($i = 11; $i >= 0; $i--) { // 12 // 11

            $month = $today->modify("-$i months");

            $fromDate = $month->modify('first day of this month');
            $toDate = $month->modify('last day of this month');

            if ($toDate > $today) {
                $toDate = $today;
            }

            $from = $fromDate->format('m/d/Y');
            $to = $toDate->format('m/d/Y');

            $monthlyResults[] = [
                'month' => $month->format('F Y'),
                'from' => $from,
                'to' => $to,
                'result' => $this->search($client, $html, $medicaid, $from, $to),
            ];
        }

        return $this->render('searchMedicaid', [
            'monthlyResults' => $monthlyResults,
        ]);
    }

    private function search(HttpClient $client, string $html, string $medicaidId, string $from, string $to): array
    {
        $eligibilityForm = new GenericForm($html);

        $fields = $eligibilityForm->eligibilityWithSearchCriteria($medicaidId, $from, $to);

        $result = $client->postReferer(PageAnalyzer::absoluteUrl($eligibilityForm->action, $client->url()), $fields, $client->url());

        $eligibility = new EligibilityResultParser($result);

        return [
            'eligibility' => [
                'status' => $eligibility->hmo['status'],
                'type' => $eligibility->hmo['type'],
            ],
            'benefit_plans' => $eligibility->listBenefitPlan,
            'managed_care' => $eligibility->listManagedCare,
            'tpl' => $eligibility->listTPL,
        ];
    }

    private function loginFLMedicaid(HttpClient $client, GenericForm $form): PageAnalysis
    {
        $fields = $form->login(App::$config['medicaid']['username'], App::$config['medicaid']['password']);

        $html = $client->postReferer("https://sso.flmmis.com" . $form->action, $fields, $client->url());

        $analyzer = new PageAnalyzer($html);

        return $analyzer->analyze($client->url());
    }

    private function processSaml(HttpClient $client, GenericForm $form): PageAnalysis
    {
        $samlUrl = PageAnalyzer::absoluteUrl($form->action, $client->url());

        $html = $client->postReferer($samlUrl, $form->fields, $client->url());

        $analyzer = new PageAnalyzer($html);

        return $analyzer->analyze($client->url());
    }

}