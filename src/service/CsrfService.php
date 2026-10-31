<?php

declare(strict_types=1);

namespace WebBase\Core\service;

use WebBase\Core\Library;
use think\Request;

/** Stable session token: concurrent forms and AJAX requests can share it. */
final class CsrfService
{
    public static function token(): string
    {
        $session = Library::$sapp->session;
        if (!$session->has('_csrf')) {
            $session->set('_csrf', bin2hex(random_bytes(32)));
        }
        return (string)$session->get('_csrf');
    }

    public static function check(Request $request): bool
    {
        if (in_array(strtoupper($request->method()), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }
        $stored = Library::$sapp->session->get('_csrf', '');
        $token = $request->header('X-CSRF-Token') ?: $request->header('User-Form-Token') ?: $request->post('_token_', '');
        return is_string($stored) && strlen($stored) === 64 && is_string($token) && hash_equals($stored, $token);
    }
}
