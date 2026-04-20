<?php

declare(strict_types=1);

namespace Zumito\ADP\Core;

class Logger
{
    private static function getLogFile(): string
    {
        $uploadDir = wp_upload_dir();
        $logDir = $uploadDir['basedir'] . '/adp-logs';

        if (!file_exists($logDir)) {
            wp_mkdir_p($logDir);
            file_put_contents($logDir . '/.htaccess', "Deny from all\n");
            file_put_contents($logDir . '/index.php', "<?php // Silence");
        }

        return $logDir . '/live.log';
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function success(string $message): void
    {
        self::write('SUCCESS', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    private static function write(string $level, string $message): void
    {
        $file = self::getLogFile();
        $date = wp_date('Y-m-d H:i:s');
        $logLine = "[{$date}] [{$level}] {$message}" . PHP_EOL;

        error_log($logLine, 3, $file);

        if (file_exists($file) && filesize($file) > 1048576) {
            $lines = file($file);
            $lines = array_slice($lines, -500);
            file_put_contents($file, implode('', $lines));
        }
    }

    public static function getRecentLogs(int $lines = 100): array
    {
        $file = self::getLogFile();
        if (!file_exists($file)) {
            return [];
        }

        $fileLines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($fileLines === false) {
            return [];
        }

        return array_slice($fileLines, -$lines);
    }

    public static function clearLogs(): void
    {
        $file = self::getLogFile();
        if (file_exists($file)) {
            file_put_contents($file, '');
        }
    }
}