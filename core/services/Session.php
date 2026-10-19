<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\App;
use app\models\User;
use Random\RandomException;

/**
 * @property int $page
 * @property int $limit
 * @property string $order
 *
 * @property-read bool $isAdmin
 *
 * @property string $_id
 * @//property string $_email
 * @property bool $isAuthenticated
 * @property int $login_attempts
 * @property int $login_time
 * @property int $last_attempt_time
 * @property array $roles
 *
 * @property string $_csrf_token
 */
final class Session
{

    const bool SESSION_STARTED = true;
    const bool SESSION_NOT_STARTED = false;

    private bool $sessionState = self::SESSION_NOT_STARTED;

    private static Session $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        self::$instance->startSession();

        return self::$instance;
    }

    public function startSession(): bool
    {
        if ($this->sessionState == self::SESSION_NOT_STARTED) {
            session_name(str_replace([' ', '-'], ['_'], App::$config['name']));
            $this->sessionState = session_start();
            session_regenerate_id(true);
        }

        return $this->sessionState;
    }

    public function destroy(): bool
    {
        if ($this->sessionState == self::SESSION_STARTED) {
            unset($_SESSION);

//            if (ini_get("session.use_cookies")) {
//                session_set_cookie_params([
//                    'secure' => true,
//                    'httponly' => true,
//                    'samesite' => 'Strict'
//                ]);
//
//                $params = session_get_cookie_params();
//
//                setcookie(session_name(), '', time() - 42000,
//                    $params["path"], $params["domain"],
//                    true, true,
////                    $params["secure"], $params["httponly"]
//                );
//            }

            $this->sessionState = !session_destroy();

            return !$this->sessionState;
        }

        return false;
    }

    public function __set(string $name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function __get(string $name)
    {
        if ($name == 'isAdmin') {
            return in_array(self::ROLE_ADMIN, $this->roles);
        }

        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return null;
    }

    public function __isset(string $name)
    {
        return isset($_SESSION[$name]);
    }

    public function __unset(string $name)
    {
        unset($_SESSION[$name]);
    }

    public function isGuest(): bool
    {
        return !$this->isAuthenticated;
    }

    public function create(User $user): void
    {
        $this->_id = $user->id;
        //$this->_email = $user->email;
        $this->isAuthenticated = true;
        $this->login_attempts = 0;
        $this->login_time = time();

        $roles = [];

        if ($user->is_admin) {
            $roles[] = self::ROLE_ADMIN;
        }

        $this->roles = $roles;
    }

    public function attemptFailed(): void
    {
        $this->isAuthenticated = false;
        $this->login_attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
        $this->last_attempt_time = time();
    }

    public function generateCSRF(bool $force = false): void
    {
        if (empty($this->_csrf_token) || $force) {
            try {
                $this->_csrf_token = bin2hex(random_bytes(32));
            } catch (RandomException $e) {
                App::$logger->error($e->getMessage());
            }
        }
    }
    public function checkCSRF(string $_csrf): bool
    {
        if (empty($this->_csrf_token)) {
            return false;
        }
        return Security::compareString($this->_csrf_token, $_csrf);
    }

    public function removeCSRF(): void
    {
        unset($this->_csrf_token);
    }

    public const string ROLE_ADMIN = 'admin';

}