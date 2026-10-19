<?php

declare(strict_types=1);

namespace app\utils\medicaid;

use app\core\App;
use app\core\exceptions\BadRequestHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\utils\medicaid\pages\AuthenticatedPage;
use app\utils\medicaid\pages\CaptchaPage;
use app\utils\medicaid\pages\DemographicMaintenancePage;
use app\utils\medicaid\pages\EligibilityPage;
use app\utils\medicaid\pages\IncorrectCredentialsPage;
use app\utils\medicaid\pages\PortalLoginPage;
use app\utils\medicaid\pages\RecipientProcedureLookupPage;
use app\utils\medicaid\pages\SamlPostPage;
use app\utils\medicaid\pages\ServiceUnavailablePage;
use app\utils\medicaid\pages\SessionExpiredPage;
use DOMElement;
use RuntimeException;

final readonly class PageAnalyzer
{

    private const int MAX_ATTEMPTS = 3;

    private const string URL_PROCEDURE_LOOKUP = 'https://portal.flmmis.com/FLPortal/Claims/Recipient%20Procedure%20Lookup/tabId/146/Default.aspx';
    private const string URL_ELIGIBILITY = 'https://portal.flmmis.com/FLPortal/Eligibility/tabId/68/Default.aspx';

    private static function analyze(string $html): GenericPage
    {
        if (str_contains($html, "Your session has expired.")) {
            return new SessionExpiredPage($html);
        }

        if (str_contains($html, "Incorrect user ID or password.")) {
            return new IncorrectCredentialsPage($html);
        }

        if (str_contains($html, "Service Unavailable")) {
            return new ServiceUnavailablePage($html);
        }

        if (str_contains($html, "Demographic Maintenance")) {
            return new DemographicMaintenancePage($html);
        }

        $page = new GenericPage($html);

        if (array_key_exists('UserName', $page->fields) && array_key_exists('Password', $page->fields)) {
            return new PortalLoginPage($html);
        }

        $formElement = $page->firstForm();

        if (!$formElement instanceof DOMElement) {
            return new AuthenticatedPage($html);
        }

        // WS-Federation/SAML automatic POST
        if (
            $formElement->getAttribute('name') === 'hiddenform'
            && array_key_exists('wa', $page->fields)
            && array_key_exists('wresult', $page->fields)
            && array_key_exists('wctx', $page->fields)
        ) {
            return new SamlPostPage($html);
        }

        // CAPTCHA
        if (str_contains($html, "CaptchaCodeTextBox")) {
            return new CaptchaPage($html);
        }

        if ($page->isEligibilityPage()) {
            return new EligibilityPage($html);
        }

        if ($page->isRecipientProcedureLookupPage()) {
            return new RecipientProcedureLookupPage($html);
        }

        return $page;
    }

    public static function absoluteUrl(string $action, string $baseUrl): string
    {
        $action = urlencode($action);

        if (preg_match('~^https?://~i', $action)) {
            return $action;
        }

        $base = parse_url($baseUrl);

        if ($base === false || !isset($base['scheme'], $base['host'])) {
            throw new RuntimeException('Unable to resolve form action.');
        }

        $origin = $base['scheme'] . '://' . $base['host'];

        if (isset($base['port'])) {
            $origin .= ':' . $base['port'];
        }

        if ($action === '') {
            return $baseUrl;
        }

        if ($action[0] === '/') {
            return $origin . $action;
        }

        $path = $base['path'] ?? '/';
        $directory = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $origin . ($directory !== '' ? $directory . '/' : '/') . ltrim($action, '/');
    }

    /**
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    public static function eligibilityPage(HttpClient $client): EligibilityPage
    {
        return self::authenticatedPage($client, self::URL_ELIGIBILITY, EligibilityPage::class);
    }

    /**
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    public static function procedureLookupPage(HttpClient $client): RecipientProcedureLookupPage
    {
        return self::authenticatedPage($client, self::URL_PROCEDURE_LOOKUP, RecipientProcedureLookupPage::class);
    }

    /**
     * @param HttpClient $client
     * @param string $url
     * @param class-string<GenericPage> $expectedPage
     * @return GenericPage|EligibilityPage|RecipientProcedureLookupPage
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    private static function authenticatedPage(HttpClient $client, string $url, string $expectedPage): GenericPage|EligibilityPage|RecipientProcedureLookupPage {
        $html = $client->get($url);

        $page = self::analyze($html);

        if ($page instanceof $expectedPage) {
            return $page;
        }

        $attempts = 0;

        do {
            if ($page instanceof ServiceUnavailablePage) {
                throw new ServerErrorHttpException('Service Unavailable');
            }

            if ($page instanceof IncorrectCredentialsPage) {
                throw new BadRequestHttpException('Incorrect portal user ID or password.');
            }

            if ($page instanceof PortalLoginPage) {
                $fields = $page->login(App::$config['medicaid']['username'], App::$config['medicaid']['password']);

                $html = $client->post('https://sso.flmmis.com' . $page->action, $fields, $client->url());
            } elseif ($page instanceof SamlPostPage) {
                $html = $client->post($page->action, $page->fields);
            } elseif ($page instanceof SessionExpiredPage) {
                $client->clearCookies();
                $client = new HttpClient('runtime/cookies/session.cookie');
                $html = $client->get($url);
            } else {
                $attempts++;
            }

            if ($attempts > self::MAX_ATTEMPTS) {
                throw new ServerErrorHttpException('Too many attempts.');
            }

            $page = self::analyze($html);

        } while (!$page instanceof $expectedPage);

        return $page;
    }

}