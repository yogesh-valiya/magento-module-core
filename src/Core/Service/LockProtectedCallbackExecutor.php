<?php

declare(strict_types=1);

namespace YValiya\Core\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Lock\LockBackendFactory;
use Magento\Framework\Lock\LockManagerInterface;
use Throwable;
use YValiya\Core\Api\LockProtectedCallbackExecutorService;
use Zend_Log;

class LockProtectedCallbackExecutor implements LockProtectedCallbackExecutorService
{
    private LockBackendFactory $lockBackendFactory;
    private ?LockManagerInterface $locker = null;

    public function __construct(
        LockBackendFactory $lockBackendFactory,
    )
    {
        $this->lockBackendFactory = $lockBackendFactory;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(callable $userFunction, string $lockName = self::DEFAULT_LOCK_NAME)
    {
        try {
            $lock = $this->getLocker()->lock($lockName);
            if (!$lock) {
                throw new LocalizedException(__("Failed to obtain lock: {$lockName}"));
            }
            $result = call_user_func($userFunction);
        } catch (Throwable $t) {
            $this->logger()->err($t->getMessage());
            $result = null;
        } finally {
            if (!$this->getLocker()->unlock($lockName)) {
                throw new LocalizedException(__("Failed to release DB lock"));
            }
        }
        return $result;
    }

    /**
     * @throws RuntimeException
     */
    private function getLocker(): LockManagerInterface
    {
        if ($this->locker === null) {
            $this->locker = $this->lockBackendFactory->create();
        }
        return $this->locker;
    }

    private function logger(): Zend_Log
    {
        return LoggerFactory::create()->get(self::ERROR_LOG_FILE_NAME);
    }
}
