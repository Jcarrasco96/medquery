<?php

declare(strict_types=1);

namespace app\models;

use app\core\Record;
use app\core\database\query\InsertSafeQuery;
use app\core\database\query\SelectSafeQuery;
use app\core\services\Security;
use Exception;
use Ramsey\Uuid\Uuid;
use Random\RandomException;

final class User extends Record
{

    const int STATUS_ACTIVE = 1;
    const int STATUS_INACTIVE = 0;
    const int STATUS_DELETED = -1;

    const string ROLE_ADMIN = 'administrator';

    public string $id;
    public string $name;
    public string $email;
    public string $password;
    public string $auth_key;

    public bool $is_admin;

    public string $status;

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

        $this->status = $data['status'] ?? (string)self::STATUS_ACTIVE;
    }

    /**
     * @throws Exception
     */
    public static function findByCredentials(string $email, string $password): ?User
    {
        $data = new SelectSafeQuery()
            ->from(self::tableName())
            ->data()
            ->where('email', $email)
            ->execute();

        $data = array_shift($data);

        if (empty($data)) {
            return null;
        }

        if (!Security::validatePassword($data['auth_key'] . $password, $data['password'])) {
            return null;
        }

        return self::fromArray($data);
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
                'email' => $this->email,
                'password' => Security::generatePasswordHash($this->auth_key . $this->password),
                'auth_key' => $this->auth_key,
                'status' => $this->status,
            ])
            ->execute();
    }

    protected static function tableName(): string
    {
        return 'user';
    }


    public function roles(): array
    {
        $roles = [];

        if ($this->is_admin) {
            $roles[] = self::ROLE_ADMIN;
        }

        return $roles;
    }

}