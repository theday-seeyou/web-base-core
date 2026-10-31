<?php

declare(strict_types=1);

namespace WebBase\Core\service;

use InvalidArgumentException;

/** Password policy shared by initialization, account creation and password changes. */
final class PasswordService
{
    public static function input(\think\Request $request): array
    {
        $input = $request->post();
        foreach (['password', 'repassword', 'oldpassword'] as $field) {
            $input[$field] = $request->post($field . '/s', '', null);
        }
        return $input;
    }
    public static function hash(string $password): string
    {
        // Bcrypt truncates after 72 bytes. Reject truncation rather than silently accepting it.
        if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
            throw new InvalidArgumentException('密码长度须为 12–72 字节，不能包含空字符。');
        }
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verify(string $password, string $hash): bool
    {
        if ($password === '' || strlen($password) > 72 || str_contains($password, "\0")) {
            return false;
        }
        return self::isLegacy($hash)
            ? hash_equals(strtolower($hash), md5($password))
            : password_verify($password, $hash);
    }

    public static function isLegacy(string $hash): bool
    {
        return preg_match('/^[a-f0-9]{32}$/i', $hash) === 1;
    }

    public static function needsRehash(string $hash): bool
    {
        return self::isLegacy($hash) || password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /** Existing short passwords may be upgraded after successful verification. */
    public static function upgrade(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
