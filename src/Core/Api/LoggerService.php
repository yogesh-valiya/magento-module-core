<?php

namespace YValiya\Core\Api;

use Zend_Log;

interface LoggerService
{
    const DEFAULT_LOG_FILE_NAME = 'general.log';
    const LOG_DIR = BP . '/var/log/' . 'yvaliya/';

    public static function get(string $fileName = self::DEFAULT_LOG_FILE_NAME): ?Zend_Log;
}