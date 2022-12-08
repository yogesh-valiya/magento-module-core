<?php

namespace YValiya\Core\Api;

interface MultiThreadExecutorService
{
    public function execute(array $userFunctions, int $maxThreadCount = null): void;
}