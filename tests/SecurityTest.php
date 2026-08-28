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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WebBase\Core\Exception;
use WebBase\Core\Storage;
use WebBase\Core\extend\HttpExtend;
use WebBase\Core\extend\ToolsExtend;
use WebBase\Core\service\ProcessService;
use WebBase\Core\support\command\Queue;

class SecurityTest extends TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webbase-core-' . bin2hex(random_bytes(8));
        mkdir($this->temporaryDirectory, 0777, true);
    }

    protected function tearDown(): void
    {
        if (isset($this->temporaryDirectory) && is_dir($this->temporaryDirectory)) {
            ToolsExtend::remove($this->temporaryDirectory);
        }
    }

    public function testCopyDoesNotOverwriteOrDeleteByDefault(): void
    {
        $source = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'source';
        $destination = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'destination';
        mkdir($source, 0777, true);
        mkdir($destination, 0777, true);
        file_put_contents($source . DIRECTORY_SEPARATOR . 'site.css', 'package-default');
        file_put_contents($destination . DIRECTORY_SEPARATOR . 'site.css', 'project-customized');

        ToolsExtend::copy($source, $destination);

        self::assertSame('project-customized', file_get_contents($destination . DIRECTORY_SEPARATOR . 'site.css'));
        self::assertFileExists($source . DIRECTORY_SEPARATOR . 'site.css');
    }

    public function testCopyOnlyOverwritesWhenExplicitlyForced(): void
    {
        $source = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'source';
        $destination = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'destination';
        mkdir($source, 0777, true);
        mkdir($destination, 0777, true);
        file_put_contents($source . DIRECTORY_SEPARATOR . 'site.css', 'package-default');
        file_put_contents($destination . DIRECTORY_SEPARATOR . 'site.css', 'project-customized');

        ToolsExtend::copy($source, $destination, [], true, false);

        self::assertSame('package-default', file_get_contents($destination . DIRECTORY_SEPARATOR . 'site.css'));
        self::assertFileExists($source . DIRECTORY_SEPARATOR . 'site.css');
    }

    public function testMoveRequiresExplicitRemoveFlag(): void
    {
        $source = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'source';
        $destination = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'destination';
        mkdir($source, 0777, true);
        file_put_contents($source . DIRECTORY_SEPARATOR . 'site.css', 'package-default');

        ToolsExtend::copy($source, $destination, [], false, true);

        self::assertSame('package-default', file_get_contents($destination . DIRECTORY_SEPARATOR . 'site.css'));
        self::assertDirectoryDoesNotExist($source);
    }

    public function testCopyRejectsPathTraversal(): void
    {
        $source = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'source';
        $destination = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'destination';
        mkdir($source, 0777, true);

        $this->expectException(\InvalidArgumentException::class);
        ToolsExtend::copy($source, $destination, ['../outside.php']);
    }

    public function testCopyRejectsNestedDestination(): void
    {
        $source = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'source';
        mkdir($source, 0777, true);

        $this->expectException(\InvalidArgumentException::class);
        ToolsExtend::copy($source, $source . DIRECTORY_SEPARATOR . 'nested');
    }

    public function testRemoveRejectsEmptyPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ToolsExtend::remove('');
    }

    public function testRemoveRejectsCurrentWorkingDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ToolsExtend::remove('.');
    }

    public function testProcessCommandBuilderKeepsArgumentsSeparate(): void
    {
        $command = ProcessService::buildCommand(['php', 'path with spaces/think', 'value & whoami']);

        self::assertStringContainsString('path with spaces/think', $command);
        self::assertStringContainsString('value & whoami', $command);
    }

    public function testRemoteRequestsRejectLocalFileProtocol(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HttpExtend::request('get', 'file:///etc/passwd');
    }

    public function testStorageDownloadRejectsLocalFileProtocol(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Storage::curlGet('file:///etc/passwd');
    }

    public function testImageUploadChecksActualFileContent(): void
    {
        $payload = 'data:image/png;base64,' . base64_encode('<?php echo "not an image";');

        $this->expectException(Exception::class);
        Storage::saveImage($payload);
    }

    #[DataProvider('invalidWebServerOptions')]
    public function testWebServerOptionsRejectShellSyntax(string $method, string $value): void
    {
        $validator = new \ReflectionMethod(Queue::class, $method);

        $this->expectException(\InvalidArgumentException::class);
        $validator->invoke(null, $value);
    }

    public static function invalidWebServerOptions(): array
    {
        return [
            ['validateHost', '127.0.0.1 & whoami'],
            ['validateHost', "localhost\nwhoami"],
            ['validatePort', '8011 & whoami'],
            ['validatePort', '70000'],
        ];
    }
}
