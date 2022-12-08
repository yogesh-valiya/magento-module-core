<?php

namespace YValiya\Core\Api;

interface LockProtectedCallbackExecutorService
{
    const DEFAULT_LOCK_NAME = 'yvaliya_callback_executor_lock';
    const ERROR_LOG_FILE_NAME = 'lock_protected_callback.log';

    public function execute(callable $userFunction, string $lockName = self::DEFAULT_LOCK_NAME);
}