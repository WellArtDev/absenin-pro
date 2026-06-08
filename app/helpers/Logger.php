<?php

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $logFile = LOG_DIR . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context) : '';
        $line = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;

        error_log($line, 3, $logFile);
    }

    public static function rotate(int $keepDays = 30): void
    {
        $files = glob(LOG_DIR . '/app-*.log');
        $cutoff = time() - ($keepDays * 86400);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
