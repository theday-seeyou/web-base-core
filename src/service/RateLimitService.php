<?php

declare(strict_types=1);

namespace WebBase\Core\service;

use RuntimeException;
use WebBase\Core\Library;

/** Atomic fixed-window limiter shared by sessions on a single application host. */
final class RateLimitService
{
    public static function hit(string $key, int $limit, int $seconds): bool
    {
        $directory = Library::$sapp->getRuntimePath() . 'security' . DIRECTORY_SEPARATOR;
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot initialize security rate limiter.');
        }
        // Fixed number of buckets limits disk usage under random username/IP attacks.
        $bucket = substr(hash('sha256', $key), 0, 2);
        $file = fopen($directory . $bucket . '.json', 'c+');
        if ($file === false) {
            throw new RuntimeException('Cannot open security rate limiter.');
        }
        try {
            if (!flock($file, LOCK_EX)) {
                throw new RuntimeException('Cannot lock security rate limiter.');
            }
            $now = time();
            $entries = json_decode(stream_get_contents($file), true) ?: [];
            $entries = array_filter($entries, static fn ($entry) => $entry['until'] > $now);
            $id = hash('sha256', $key);
            $entry = $entries[$id] ?? ['until' => $now + $seconds, 'count' => 0];
            $entry['count']++;
            $entries[$id] = $entry;
            rewind($file);
            ftruncate($file, 0);
            fwrite($file, json_encode($entries, JSON_THROW_ON_ERROR));
            fflush($file);
            return $entry['count'] <= $limit;
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }
}
