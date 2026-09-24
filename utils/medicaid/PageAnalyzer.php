<?php

declare(strict_types=1);

namespace app\utils\medicaid;

use DOMElement;
use RuntimeException;

final readonly class PageAnalyzer
{

    private DomParser $dom;

    public function __construct(private string $html)
    {
        $this->dom = new DOMParser($html);
    }

    public function analyze(string $url): PageAnalysis
    {
        if (str_contains($this->html, "Your session has expired.")) {
            return new PageAnalysis(PageState::SESSION_EXPIRED);
        }

        if (str_contains($this->html, "Incorrect user ID or password.")) {
            return new PageAnalysis(PageState::INCORRECT_CREDENTIALS);
        }

        $formElement = $this->dom->firstForm();

        if (!$formElement instanceof DOMElement) {
            return new PageAnalysis(PageState::AUTHENTICATED);
        }

        $form = new GenericForm($this->html);

        // LOGIN
        if (array_key_exists('UserName', $form->fields) && array_key_exists('Password', $form->fields)) {
            return new PageAnalysis(PageState::LOGIN, $form);
        }

        // WS-Federation/SAML automatic POST
        if (
            $formElement->getAttribute('name') === 'hiddenform'
            && array_key_exists('wa', $form->fields)
            && array_key_exists('wresult', $form->fields)
            && array_key_exists('wctx', $form->fields)
        ) {
            return new PageAnalysis(PageState::SAML_POST, $form);
        }

        // CAPTCHA
        if (str_contains($this->html, "CaptchaCodeTextBox")) {
            // id img c_botchallengepage_captchacontrol_CaptchaImage
            return new PageAnalysis(PageState::CAPTCHA, $form, $this->dom->images());
        }

        if ($this->dom->isEligibilityPage()) {
            return new PageAnalysis(PageState::ELIGIBILITY, $form);
        }

        return new PageAnalysis(PageState::UNKNOWN, $form);
    }

    public static function absoluteUrl(string $action, string $baseUrl): string
    {
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
}