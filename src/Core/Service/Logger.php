<?php

namespace YValiya\Core\Service;

use Magento\Framework\Filesystem\Io\File;
use YValiya\Core\Api\LoggerService;
use Zend_Log;
use Zend_Log_Writer_Stream;

class Logger implements LoggerService
{
    private static array $loggers = [];

    public static function get(string $fileName = self::DEFAULT_LOG_FILE_NAME): ?Zend_Log
    {
        if (!isset(self::$loggers[$fileName])) {
            $fileFullPath = self::LOG_DIR . $fileName;
            self::initDirectory($fileFullPath);
            try {
                $writer = new Zend_Log_Writer_Stream($fileFullPath);
                $logger = new Zend_Log();
                $logger->addWriter($writer);
                self::$loggers[$fileName] = $logger;
            } catch (\Zend_Log_Exception) {
                // Suppress
            }
        }
        return self::$loggers[$fileName] ?? null;
    }

    private static function initDirectory($directory)
    {
        $file = new File();
        $directory = dirname($directory);
        if (!file_exists($directory)) {
            $file->mkdir($directory);
        }
    }

    public static function markTime(float $diffFrom, int $precision = 2): array
    {
        $current = self::time($precision);
        return [$current, round(($current - $diffFrom), $precision)];
    }

    public static function time(int $precision = 2): float
    {
        return round(microtime(true), $precision);
    }
}
