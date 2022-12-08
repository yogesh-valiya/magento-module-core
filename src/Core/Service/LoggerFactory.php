<?php

declare(strict_types=1);

namespace YValiya\Core\Service;

use Magento\Framework\App\ObjectManager;
use YValiya\Core\Api\LoggerService;

class LoggerFactory
{
    public static function create(): LoggerService
    {
        return ObjectManager::getInstance()->create(LoggerService::class);
    }
}
