<?php
namespace Forkel;

/**
 * Lightweight sliding-window rate limiter for AJAX endpoints.
 */
final class RateLimiter
{
    public static function check(string $bucket, int $maxRequests = 20, int $windowSec = 60): bool
    {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $key = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $bucket . '_' . $ip);
        $dir = sys_get_temp_dir() . '/forkel_rl';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file = $dir . '/' . md5($key) . '.json';
        $now = time();

        $fp = @fopen($file, 'c+');
        if (!$fp) {
            return true;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return true;
        }

        $raw = stream_get_contents($fp);
        $hits = $raw ? json_decode($raw, true) : [];
        if (!is_array($hits)) {
            $hits = [];
        }
        $hits = array_values(array_filter($hits, static fn($ts) => is_int($ts) && ($now - $ts) < $windowSec));

        $allowed = count($hits) < $maxRequests;
        if ($allowed) {
            $hits[] = $now;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($hits));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $allowed;
    }
}
