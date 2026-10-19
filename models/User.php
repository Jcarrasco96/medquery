<?php

declare(strict_types=1);

namespace app\models;

use app\core\database\query\DeleteSafeQuery;
use app\core\exceptions\ServerErrorHttpException;
use app\core\Record;
use app\core\database\query\InsertSafeQuery;
use app\core\database\query\SelectSafeQuery;
use app\core\services\Security;
use app\core\validators\EmailValidator;
use app\core\validators\RequiredValidator;
use Exception;
use Ramsey\Uuid\Uuid;
use Random\RandomException;

final class User extends Record
{

    const int STATUS_ACTIVE = 1;
    const int STATUS_INACTIVE = 0;
    const int STATUS_DELETED = -1;

    public string $id;
    public string $name;
    public string $email;
    public string $password;
    public string $auth_key;

    public bool $is_admin = false;

    public string $status;

    public ?string $portal_username;

    /**
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? Uuid::uuid4()->toString();

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }

        $this->auth_key = $data['auth_key'] ?? Security::generateRandomString();
        $this->is_admin = $data['is_admin'] ?? false;
        $this->status = $data['status'] ?? (string)self::STATUS_ACTIVE;
    }

    public static function delete(string $id): bool
    {
        return new DeleteSafeQuery()
            ->from(self::tableName())
            ->where('id', $id)
            ->execute();
    }

    public function rules(): array
    {
        return [
            'name' => [
                new RequiredValidator(),
            ],
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
     * @throws ServerErrorHttpException
     */
    public static function findByCredentials(string $email, string $password): ?User
    {
        $data = new SelectSafeQuery()
            ->from(self::tableName())
            ->data()
            ->where('email', $email)
            ->execute();

        $data = array_shift($data);

        if (empty($data) || !Security::validatePassword($data['auth_key'] . $password, $data['password'])) {
            return null;
        }

        return self::fromArray($data);
    }

    public static function findByUserId(string $id): ?User
    {
        $data = new SelectSafeQuery()
            ->from(self::tableName())
            ->data()
            ->where('user_id', $id)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            return null;
        }

        return self::fromArray(array_shift($data));
    }

    public static function findAll(): array
    {
        $data = new SelectSafeQuery()
            ->from(self::tableName())
            ->data()
            ->execute();

        return array_map(static fn(array $data) => self::fromArray($data), $data);
    }

    /**
     * @throws RandomException
     */
    public function create(): bool
    {
        return new InsertSafeQuery()
            ->from(self::tableName())
            ->data([
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'password' => Security::generatePasswordHash($this->auth_key . $this->password),
                'auth_key' => $this->auth_key,
                'is_admin' => (int)$this->is_admin,
                'status' => $this->status,
            ])
            ->execute();
    }

    protected static function tableName(): string
    {
        return 'user';
    }

    public function validate(bool $clearErrors = true): bool
    {
        $isValid = parent::validate($clearErrors);

        if ($isValid) {
            if (User::exists('email', $this->email)) {
                $this->addError('email', 'Email already exists.');
                $this->email = '';
            }
        }

        return empty($this->errors);
    }


}