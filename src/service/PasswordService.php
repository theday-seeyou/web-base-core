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
        if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
            return false;
        }
        return password_get_info($hash)['algo'] === PASSWORD_BCRYPT && password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
