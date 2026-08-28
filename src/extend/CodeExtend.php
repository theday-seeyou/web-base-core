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

namespace WebBase\Core\extend;

/**
 * 随机数码管理扩展.
 * @class CodeExtend
 */
class CodeExtend
{
    private const ENCRYPTION_PREFIX = 'v2.';

    private const ENCRYPTION_CIPHER = 'aes-256-gcm';

    private const ENCRYPTION_IV_LENGTH = 12;

    private const ENCRYPTION_TAG_LENGTH = 16;

    private const MAX_DECOMPRESSED_BYTES = 16777216;

    /**
     * 生成 UUID 编码
     */
    public static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = strtoupper(bin2hex($bytes));
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * 生成随机编码
     * @param int $size 编码长度
     * @param int $type 编码类型(1纯数字,2纯字母,3数字字母)
     * @param string $prefix 编码前缀
     */
    public static function random(int $size = 10, int $type = 1, string $prefix = ''): string
    {
        if ($size < 1) {
            throw new \InvalidArgumentException('Random code length must be greater than zero.');
        }
        $chars = match ($type) {
            1 => '0123456789',
            2 => 'abcdefghijklmnopqrstuvwxyz',
            3 => '0123456789abcdefghijklmnopqrstuvwxyz',
            default => throw new \InvalidArgumentException('Random code type must be 1, 2 or 3.'),
        };
        $code = substr($prefix, 0, $size);
        while (strlen($code) < $size) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * 生成日期编码
     * @param int $size 编码长度
     * @param string $prefix 编码前缀
     */
    public static function uniqidDate(int $size = 16, string $prefix = ''): string
    {
        if ($size < 14) {
            $size = 14;
        }
        $code = $prefix . date('Ymd') . (date('H') + date('i')) . date('s');
        while (strlen($code) < $size) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     * 生成数字编码
     * @param int $size 编码长度
     * @param string $prefix 编码前缀
     */
    public static function uniqidNumber(int $size = 12, string $prefix = ''): string
    {
        $time = strval(time());
        if ($size < 10) {
            $size = 10;
        }
        $code = $prefix . (intval($time[0]) + intval($time[1])) . substr($time, 2) . rand(0, 9);
        while (strlen($code) < $size) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     * 文本转码
     * @param string $text 文本内容
     * @param string $target 目标编码
     */
    public static function text2utf8(string $text, string $target = 'UTF-8'): string
    {
        [$first2, $first4] = [substr($text, 0, 2), substr($text, 0, 4)];
        if ($first4 === chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF)) {
            $ft = 'UTF-32BE';
        } elseif ($first4 === chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00)) {
            $ft = 'UTF-32LE';
        } elseif ($first2 === chr(0xFE) . chr(0xFF)) {
            $ft = 'UTF-16BE';
        } elseif ($first2 === chr(0xFF) . chr(0xFE)) {
            $ft = 'UTF-16LE';
        }
        return mb_convert_encoding($text, $target, $ft ?? mb_detect_encoding($text));
    }

    /**
     * 数据加密处理.
     * @param mixed $data 加密数据
     * @param string $skey 安全密钥
     */
    public static function encrypt($data, string $skey): string
    {
        $iv = random_bytes(self::ENCRYPTION_IV_LENGTH);
        $value = openssl_encrypt(
            serialize($data),
            self::ENCRYPTION_CIPHER,
            hash('sha256', $skey, true),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::ENCRYPTION_TAG_LENGTH
        );
        if ($value === false) {
            throw new \RuntimeException('Unable to encrypt data.');
        }
        $payload = json_encode([
            'iv' => static::enSafe64($iv),
            'tag' => static::enSafe64($tag),
            'value' => static::enSafe64($value),
        ], JSON_THROW_ON_ERROR);
        return self::ENCRYPTION_PREFIX . static::enSafe64($payload);
    }

    /**
     * 数据解密处理.
     * @param string $data 解密数据
     * @param string $skey 安全密钥
     * @return mixed
     */
    public static function decrypt(string $data, string $skey)
    {
        try {
            if (str_starts_with($data, self::ENCRYPTION_PREFIX)) {
                $attr = json_decode(static::deSafe64(substr($data, strlen(self::ENCRYPTION_PREFIX))), true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($attr) || !isset($attr['iv'], $attr['tag'], $attr['value'])) {
                    throw new \UnexpectedValueException('Invalid encrypted payload.');
                }
                $value = openssl_decrypt(
                    static::deSafe64($attr['value']),
                    self::ENCRYPTION_CIPHER,
                    hash('sha256', $skey, true),
                    OPENSSL_RAW_DATA,
                    static::deSafe64($attr['iv']),
                    static::deSafe64($attr['tag'])
                );
                if ($value === false) {
                    throw new \UnexpectedValueException('Encrypted payload authentication failed.');
                }
                return static::unserializeValue($value);
            }

            $attr = json_decode(static::deSafe64($data), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($attr) || !isset($attr['iv'], $attr['value'])) {
                throw new \UnexpectedValueException('Invalid legacy encrypted payload.');
            }
            $value = openssl_decrypt($attr['value'], 'AES-256-CBC', $skey, 0, $attr['iv']);
            if ($value === false) {
                throw new \UnexpectedValueException('Unable to decrypt legacy payload.');
            }
            return static::unserializeValue($value);
        } catch (\UnexpectedValueException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new \UnexpectedValueException('Unable to decrypt data.', 0, $exception);
        }
    }

    /**
     * Base64Url 安全编码
     * @param string $text 待加密文本
     */
    public static function enSafe64(string $text): string
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }

    /**
     * Base64Url 安全解码
     * @param string $text 待解密文本
     */
    public static function deSafe64(string $text): string
    {
        $value = base64_decode(str_pad(strtr($text, '-_', '+/'), (int)(ceil(strlen($text) / 4) * 4), '='), true);
        if ($value === false) {
            throw new \UnexpectedValueException('Invalid Base64Url data.');
        }
        return $value;
    }

    /**
     * 压缩数据对象
     * @param mixed $data
     */
    public static function enzip($data): string
    {
        $value = gzcompress(serialize($data));
        if ($value === false) {
            throw new \RuntimeException('Unable to compress data.');
        }
        return static::enSafe64($value);
    }

    /**
     * 解压数据对象
     * @return mixed
     */
    public static function dezip(string $string)
    {
        $value = gzuncompress(static::deSafe64($string), self::MAX_DECOMPRESSED_BYTES);
        if ($value === false) {
            throw new \UnexpectedValueException('Unable to decompress data.');
        }
        return static::unserializeValue($value);
    }

    /**
     * 安全解析内部序列化数据，不允许实例化对象。
     * @return mixed
     */
    private static function unserializeValue(string $value)
    {
        set_error_handler(static function (int $severity, string $message): never {
            throw new \UnexpectedValueException($message, $severity);
        });
        try {
            $result = unserialize($value, ['allowed_classes' => false]);
        } finally {
            restore_error_handler();
        }
        if ($result === false && $value !== 'b:0;') {
            throw new \UnexpectedValueException('Invalid serialized data.');
        }
        return $result;
    }
}
