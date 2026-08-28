<?php

declare(strict_types=1);
/**
 * +----------------------------------------------------------------------
 * | WebBase Core Component
 * +----------------------------------------------------------------------
 * | Copyright (c) 2026 WebBase contributors
 * +----------------------------------------------------------------------
 * | 官方网站: https://github.com/theday-seeyou/web-base-core
 * +----------------------------------------------------------------------
 * | 开源协议 ( https://mit-license.org )
 * +----------------------------------------------------------------------
 * | github 代码仓库：https://github.com/theday-seeyou/web-base-core
 * +----------------------------------------------------------------------
 */

namespace WebBase\Core\tests;

use PHPUnit\Framework\TestCase;
use WebBase\Core\extend\CodeExtend;

class CodeTest extends TestCase
{
    public function testUuidCreate()
    {
        $uuid = CodeExtend::uuid();
        $this->assertMatchesRegularExpression('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/', $uuid);
    }

    public function testEncode()
    {
        $value = ['id' => 10000, 'name' => 'WebBase'];
        $encode = CodeExtend::encrypt($value, 'webbase');
        $this->assertStringStartsWith('v2.', $encode);
        $this->assertEquals($value, CodeExtend::decrypt($encode, 'webbase'), '验证加密解密');
    }

    public function testLegacyCiphertextCanStillBeDecoded()
    {
        $value = 'legacy-session-id';
        $iv = '1234567890abcdef';
        $legacy = CodeExtend::enSafe64(json_encode([
            'iv' => $iv,
            'value' => openssl_encrypt(serialize($value), 'AES-256-CBC', 'webbase', 0, $iv),
        ], JSON_THROW_ON_ERROR));

        $this->assertSame($value, CodeExtend::decrypt($legacy, 'webbase'));
    }

    public function testTamperedCiphertextIsRejected()
    {
        $encoded = CodeExtend::encrypt('protected-value', 'webbase');
        $index = intdiv(strlen($encoded), 2);
        $encoded[$index] = $encoded[$index] === 'A' ? 'B' : 'A';

        $this->expectException(\UnexpectedValueException::class);
        CodeExtend::decrypt($encoded, 'webbase');
    }

    public function testCompressionRoundTrip()
    {
        $value = ['language' => ['zh-cn', 'en-us'], 'enabled' => true];
        $this->assertSame($value, CodeExtend::dezip(CodeExtend::enzip($value)));
    }

    public function testRandomUsesRequestedAlphabetAndLength()
    {
        $this->assertMatchesRegularExpression('/^[0-9]{16}$/', CodeExtend::random(16, 1));
        $this->assertMatchesRegularExpression('/^[a-z]{16}$/', CodeExtend::random(16, 2));
        $this->assertMatchesRegularExpression('/^[0-9a-z]{16}$/', CodeExtend::random(16, 3));
    }
}
