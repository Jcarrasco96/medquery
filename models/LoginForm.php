<?php

declare(strict_types=1);

namespace app\models;

use app\core\App;
use app\core\Model;
use app\core\validators\EmailValidator;
use app\core\validators\RequiredValidator;
use Exception;

final class LoginForm extends Model
{

    public string $email;
    public string $password;

    public ?User $user;

    public function rules(): array
    {
        return [
            'email' => [
                new RequiredValidator(),
                new EmailValidator(),
            ],
            'password'  => [
                new RequiredValidator(),
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function login(): bool
    {
        $this->user = User::findByCredentials($this->email, $this->password);

        if (!$this->user) {
            App::$session->attemptFailed();
            $this->logLoginAttempt($this->email, false);

            $this->addError('password', 'Email or password is incorrect.');

            return false;
        }

        App::$session->removeCSRF();
        App::$session->create($this->user->id, $this->user->email, $this->user->roles());
        App::$user = $this->user;
        $this->logLoginAttempt($this->email);

        return true;
    }

    private function logLoginAttempt(string $username, bool $success = true): void
    {
        $logEntry = date('Y-m-d H:i:s') . " - User: $username - " . ($success ? "SUCCESS" : "FAILED") . "\n";
        file_put_contents(APP_LOGS_FOLDER . 'login_attempts.log', $logEntry, FILE_APPEND | LOCK_EX);
    }

}