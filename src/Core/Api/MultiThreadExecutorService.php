<?php

namespace YValiya\Core\Api;

interface MultiThreadExecutorService
{
    const DEFAULT_THREAD_COUNT = 8;
    const ERROR_LOG_FILE_NAME = 'multi_threading.log';

    public function execute(array $userFunctions, int $maxThreadCount = null): void;
}