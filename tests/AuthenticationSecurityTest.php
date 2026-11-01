<?php

declare(strict_types=1);

namespace WebBase\Core\tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WebBase\Core\Library;
use WebBase\Core\service\CaptchaService;
use WebBase\Core\service\CsrfService;
use WebBase\Core\service\PasswordService;
use WebBase\Core\service\RateLimitService;
use WebBase\Core\service\UploadPolicy;
use think\App;
use think\Request;

final class AuthenticationSecurityTest extends TestCase
{
    public function testPasswordsAcceptModernHashesAndRejectMd5(): void
    {
        self::assertFalse(PasswordService::verify('A long legacy password', md5('A long legacy password')));
        self::assertFalse(PasswordService::verify('A long legacy password', strtoupper(md5('A long legacy password'))));
        self::assertFalse(PasswordService::verify('A long legacy password', '$1$salt$invalid-md5-crypt'));
        $hash = PasswordService::hash('A long test password 123!');
        self::assertTrue(PasswordService::verify('A long test password 123!', $hash));
        self::assertFalse(PasswordService::verify('wrong', $hash));
        self::assertFalse(PasswordService::needsRehash($hash));
        $olderCost = password_hash('A long test password 123!', PASSWORD_BCRYPT, ['cost' => 10]);
        self::assertTrue(PasswordService::verify('A long test password 123!', $olderCost));
        self::assertTrue(PasswordService::needsRehash($olderCost));
        self::assertFalse(PasswordService::verify('short', password_hash('short', PASSWORD_BCRYPT)));
    }

    public function testNewPasswordsCannotSilentlyTruncate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PasswordService::hash(str_repeat('x', 73));
    }

    public function testShortNewPasswordsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PasswordService::hash('admin');
    }

    public function testCsrfIsStableAndRejectsMissingOrWrongToken(): void
    {
        $app = $this->app();
        $token = CsrfService::token();
        self::assertSame($token, CsrfService::token());
        $request = new Request();
        $request->setMethod('POST');
        $request->withHeader(['x-csrf-token' => $token]);
        self::assertTrue(CsrfService::check($request));
        self::assertTrue(CsrfService::check($request));
        $app->session->delete('_csrf');
        self::assertFalse(CsrfService::check($request));
    }

    public function testCaptchaConsumesFailureAndNeverMatchesMissingChallenge(): void
    {
        $app = $this->app();
        self::assertFalse(CaptchaService::check('', 'missing'));
        $app->cache->set('test-challenge', 'ABCD', 60);
        self::assertFalse(CaptchaService::check('wrong', 'test-challenge'));
        self::assertFalse(CaptchaService::check('ABCD', 'test-challenge'));
    }

    public function testRateLimitSurvivesNewSession(): void
    {
        $app = $this->app();
        $key = 'test:' . bin2hex(random_bytes(8));
        self::assertTrue(RateLimitService::hit($key, 2, 60));
        $app->session->regenerate(true);
        self::assertTrue(RateLimitService::hit($key, 2, 60));
        self::assertFalse(RateLimitService::hit($key, 2, 60));
    }

    public function testUploadRejectsSpoofedPdfAndOversizeMetadata(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'wb-upload-');
        file_put_contents($path, '<?php echo "disguised";');
        try {
            try {
                UploadPolicy::assertFile($path, 'pdf', ['pdf']);
                self::fail('A PHP script disguised as PDF must be rejected.');
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString('类型', $exception->getMessage());
            }
            $this->expectException(InvalidArgumentException::class);
            UploadPolicy::assertMetadata('pdf', UploadPolicy::MAX_BYTES + 1, ['pdf']);
        } finally {
            unlink($path);
        }
    }

    private function app(): App
    {
        $app = new App();
        $path = sys_get_temp_dir() . '/webbase-security-tests/';
        $app->setRuntimePath($path);
        $app->config->set(['type' => 'file', 'name' => 'test-session'], 'session');
        $app->config->set(['default' => 'file', 'stores' => ['file' => ['type' => 'file', 'path' => $path . 'cache/']]], 'cache');
        $app->session->init();
        Library::$sapp = $app;
        return $app;
    }
}
