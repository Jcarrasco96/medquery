<?php

declare(strict_types=1);

namespace app\core\services;

use Exception;
use Random\RandomException;

final class Security
{

    /**
     * @throws Exception
     */
    public static function generateRandomString(int $length = 32): string
    {
        if ($length < 1) {
            throw new Exception('First parameter ($length) must be greater than 0');
        }

        return substr(self::base64UrlEncode(self::generateRandomKey($length)), 0, $length);
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    private static function generateRandomKey(int $length = 32): string
    {
        if ($length < 1) {
            throw new Exception('First parameter ($length) must be greater than 0');
        }

        return random_bytes($length);
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    public static function generatePasswordHash(string $password, int $cost = 13): string
    {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
        }

        $salt = self::generateSalt($cost);
        $hash = crypt($password, $salt);
        // strlen() is safe since crypt() returns only ascii
        if (strlen($hash) !== 60) {
            throw new Exception('Unknown error occurred while generating hash.');
        }

        return $hash;
    }

    /**
     * @throws Exception
     */
    public static function validatePassword(string $password, string $hash): bool
    {
        if (!preg_match('/^\$2[axy]\$(\d\d)\$[.\/0-9A-Za-z]{22}/', $hash, $matches) || $matches[1] < 4 || $matches[1] > 30) {
            throw new Exception('Hash is invalid.');
        }

        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }

        $test = crypt($password, $hash);
        $n = strlen($test);
        if ($n !== 60) {
            return false;
        }

        return self::compareString($test, $hash);
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    private static function generateSalt(int $cost = 13): string
    {
        if ($cost < 4 || $cost > 31) {
            throw new Exception('Cost must be between 4 and 31.');
        }

        $rand = self::generateRandomKey(20);
        // Form the prefix that specifies Blowfish (bcrypt) algorithm and cost parameter.
        $salt = sprintf('$2y$%02d$', $cost);
        // Append the random salt data in the required base64 format.
        $salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));

        return $salt;
    }

    public static function compareString(string $expected, string $actual): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = self::byteLength($expected);
        $actualLength = self::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }

        return $diff === 0;
    }

    private static function byteLength(string $string): int
    {
        return mb_strlen($string, '8bit');
    }

    private static function base64UrlEncode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * Generate a password passing the format as a parameter
     *
     * ?: Any
     * #: Number
     * //A: Letter (upper)
     * //a: Letter (lower)
     * .: Letter
     * -: Minus symbol used as a separator
     *
     * @param string $pattern
     * @return string
     */
    public static function generatePassword(string $pattern = '??????-??????-??????'): string
    {
        $ret = '';

//        $uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
//        $lowers = 'abcdefghijklmnopqrstuvwxyz';

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

//        str_shuffle($uppers);
//        str_shuffle($lowers);
        str_shuffle($characters);

        for ($i = 0; $i < strlen($pattern); $i++) {
            $ret .= match ($pattern[$i]) {
                '?' => rand(0, 1) == 1 ? rand(0, 9) : $characters[rand(0, strlen($characters) - 1)],
                '#' => rand(0, 9),
                '.' => $characters[rand(0, strlen($characters) - 1)],
//                'A' => $uppers[rand(0, strlen($uppers) - 1)],
//                'a' => $lowers[rand(0, strlen($lowers) - 1)],
                '-' => '-',
            };
        }

        return $ret;
    }

    /**
     * @throws RandomException
     */
    public static function encrypt(string $data, string $key, string $method = 'aes-256-cbc'): array
    {
        $iv = bin2hex(random_bytes(8));

        return [
            'data' => base64_encode(openssl_encrypt($data, $method, $key, 0, $iv)),
            'iv' => base64_encode($iv),
        ];
    }

    public static function decrypt(string $data, string $iv, string $key, string $method = 'aes-256-cbc'): string
    {
        return openssl_decrypt(base64_decode($data), $method, $key, 0, base64_decode($iv));
    }

}