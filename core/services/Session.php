<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\App;
use app\models\User;
use Exception;

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

    public function isAuthenticated(): bool
    {
//        try {
//            $exists = User::findById($this->_id());
//
//            if (!$exists) {
//                return false;
//            }
//        } catch (NotFoundHttpException $e) {
//            App::$logger->info("User not found in session. " . $e->getMessage());
//        }

        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public function isGuest(): bool
    {
        return !$this->isAuthenticated();
    }

    public function create(string $id, string $email, array $roles = []): void
    {
        $_SESSION['_id'] = $id;
        $_SESSION['_email'] = $email;
        $_SESSION['authenticated'] = true;
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_time'] = time();

        $_SESSION['roles'] = $roles;

        if (count($roles) > 0) {
            $this->setSelectedRole($roles[0]);
        }

//        $_SESSION['last_permission_check'] = time();
    }

    public function attemptFailed(): void
    {
        $_SESSION['authenticated'] = false;
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
        $_SESSION['last_attempt_time'] = time();
    }

    public function _id(): string
    {
        return $_SESSION['_id'] ?? '';
    }

    /**
     * @throws Exception
     */
    public function generateCSRF(bool $force = false): void
    {
        if (empty($_SESSION['_csrf_token']) || $force) {
//            $_SESSION['_csrf_token'] = Security::generateRandomString();
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function _csrf(): string
    {
        return $_SESSION['_csrf_token'] ?? '';
    }

    public function checkCSRF(string $_csrf): bool
    {
        if (empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return Security::compareString($_SESSION['_csrf_token'], $_csrf);
    }

    public function removeCSRF(): void
    {
        if (!empty($_SESSION['_csrf_token'])) {
            unset($_SESSION['_csrf_token']);
        }
    }

    public function can(string|array|null $role): bool
    {
        if (!isset($role)) {
            return false;
        }

        if (is_string($role)) {
            return in_array($role, $this->roles());
        }

        return array_any($role, fn($r) => in_array($r, $this->roles()));
    }

    public function canStrict(string|array $role): bool
    {
        if (is_string($role)) {
            return $this->getSelectedRole() === $role && $this->can($role);
        }

        if (is_array($role)) {
            if (array_any($role, fn($r) => $this->getSelectedRole() === $r && $this->can($r))) {
                return true;
            }
        }

        return false;
    }

    public function setSelectedRole(string $role): void
    {
        if (in_array($role, $this->roles())) {
            $_SESSION['selected_role'] = $role;
        }
    }

    public function clearSelectedRole(): void
    {
        unset($_SESSION['selected_role']);
    }

    public function getSelectedRole(): ?string
    {
        return $_SESSION['selected_role'] ?? null;
    }

    public function roles(): array
    {
        $user = User::findById($this->_id());

        if ($user) {
            $_SESSION['roles'] = $user->roles();
        } else {
            $_SESSION['roles'] = [];
        }

        return $_SESSION['roles'];
    }

}