<?php

declare(strict_types=1);

namespace WebBase\Core\service;

use InvalidArgumentException;

final class UploadPolicy
{
    public const MAX_BYTES = 52428800;

    public static function assertMetadata(string $extension, int $size, array $allowed, int $maxBytes = self::MAX_BYTES): void
    {
        if ($size < 1 || $size > $maxBytes) {
            throw new InvalidArgumentException('文件为空或超过允许的上传大小。');
        }
        if (!in_array($extension, $allowed, true) || !isset(self::mimeTypes()[$extension])) {
            throw new InvalidArgumentException('文件类型不允许上传。');
        }
    }

    public static function assertFile(string $path, string $extension, array $allowed, int $maxBytes = self::MAX_BYTES): void
    {
        self::assertMetadata($extension, (int)filesize($path), $allowed, $maxBytes);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, self::mimeTypes()[$extension], true)) {
            throw new InvalidArgumentException('文件实际类型与扩展名不符。');
        }
        if (in_array($extension, ['jpg', 'jpeg', 'gif', 'png', 'webp', 'ico'], true) && @getimagesize($path) === false) {
            throw new InvalidArgumentException('图片内容无法识别。');
        }
        if ($extension === 'pdf' && file_get_contents($path, false, null, 0, 5) !== '%PDF-') {
            throw new InvalidArgumentException('PDF 文件内容无法识别。');
        }
        if (in_array($extension, ['docx', 'xlsx'], true)) {
            if (!class_exists(\ZipArchive::class)) {
                throw new InvalidArgumentException('服务器需要启用 ZIP 扩展以校验 Office 文档。');
            }
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                throw new InvalidArgumentException('Office 文档格式错误。');
            }
            try {
                $entry = $extension === 'docx' ? 'word/document.xml' : 'xl/workbook.xml';
                if ($zip->locateName('[Content_Types].xml') === false || $zip->locateName($entry) === false) {
                    throw new InvalidArgumentException('Office 文档内容与扩展名不符。');
                }
            } finally {
                $zip->close();
            }
        }
    }

    private static function mimeTypes(): array
    {
        return [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
            'gif' => ['image/gif'], 'webp' => ['image/webp'], 'ico' => ['image/x-icon', 'image/vnd.microsoft.icon'],
            'doc' => ['application/msword', 'application/CDFV2'],
            'xls' => ['application/vnd.ms-excel', 'application/CDFV2'],
            'docx' => ['application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xlsx' => ['application/zip', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'zip' => ['application/zip', 'application/x-zip'],
            'rar' => ['application/x-rar', 'application/x-rar-compressed', 'application/vnd.rar'],
            'mp3' => ['audio/mpeg', 'audio/mp3'], 'mp4' => ['video/mp4', 'application/mp4'],
        ];
    }
}
