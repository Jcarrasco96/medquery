<?php

declare(strict_types=1);

namespace app\utils\medicaid;

enum PageState: string
{
    case LOGIN = 'login';
    case SAML_POST = 'saml_post';
    case CAPTCHA = 'captcha';
    case ELIGIBILITY = 'eligibility';
    case AUTHENTICATED = 'authenticated';
    case UNKNOWN = 'unknown';
    case SESSION_EXPIRED = 'session_expired';
    case INCORRECT_CREDENTIALS = 'incorrect_credentials';
}