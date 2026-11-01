<?php

declare(strict_types=1);

namespace WebBase\Core\tests;

use PHPUnit\Framework\TestCase;
use think\App;
use think\Config;
use think\Lang;
use think\Request;

class LanguageTest extends TestCase
{
    public function testStructuredLanguageInputsFallBackWithoutThrowing(): void
    {
        $app = new App();
        $lang = new Lang($app, ['allow_lang_list' => ['zh-cn', 'en-us']]);
        $middleware = new class($app, $lang, new Config()) extends \WebBase\Core\support\middleware\LoadLangPack {
            public function detectForTest(Request $request): string
            {
                return $this->detect($request);
            }
        };
        foreach (['withGet' => 'lang', 'withCookie' => 'think_lang'] as $source => $key) {
            $request = (new Request())->{$source}([$key => ['en-us']]);
            self::assertSame('zh-cn', $middleware->detectForTest($request));
        }
        $request = (new Request())->withGet(['lang' => 'en-us']);
        self::assertSame('en-us', $middleware->detectForTest($request));
    }
}
