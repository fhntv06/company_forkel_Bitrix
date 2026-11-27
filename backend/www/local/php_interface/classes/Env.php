<?php
namespace Forkel;

/**
 * Simple environment helper reading process env with optional fallback to backend/.env.
 */
final class Env
{
    private static ?array $fileVars = null;

    public static function get(string $key, string $default = ''): string
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string)$val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }
        $vars = self::loadFile();
        if (isset($vars[$key]) && $vars[$key] !== '') {
            return $vars[$key];
        }
        return $default;
    }

    private static function loadFile(): array
    {
        if (self::$fileVars !== null) {
            return self::$fileVars;
        }
        self::$fileVars = [];
        $candidates = [
            dirname($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html') . '/.env',
            ($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/html') . '/.env',
        ];
        foreach ($candidates as $path) {
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!is_array($lines)) {
                continue;
            }
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                if (
                    (str_starts_with($v, '"') && str_ends_with($v, '"'))
                    || (str_starts_with($v, "'") && str_ends_with($v, "'"))
                ) {
                    $v = substr($v, 1, -1);
                }
                if ($k !== '') {
                    self::$fileVars[$k] = $v;
                }
            }
            break;
        }
        return self::$fileVars;
    }
}
