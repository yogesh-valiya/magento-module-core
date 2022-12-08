<?php

/** @noinspection ALL */

declare(strict_types=1);

namespace YValiya\Core\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Throwable;
use YValiya\Core\Api\MultiThreadExecutorService;
use Zend_Log;

class MultiThreadExecutor implements MultiThreadExecutorService
{
    private bool $failInChildProcess = false;
    private ResourceConnection $resource;
    private ?int $threadsCount;

    public function __construct(
        ResourceConnection $resource
    )
    {
        $this->resource = $resource;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(array $userFunctions, int $maxThreadCount = null): void
    {
        $this->threadsCount = $maxThreadCount === null ? self::DEFAULT_THREAD_COUNT : $maxThreadCount;

        if ($this->isCanBeParalleled() && PHP_SAPI == 'cli') {
            $this->multiThreadsExecute($userFunctions);
        } else {
            $this->singleThreadExecute($userFunctions);
        }
    }

    private function isCanBeParalleled(): bool
    {
        return function_exists('pcntl_fork') && $this->threadsCount > 1;
    }

    /**
     * @throws LocalizedException
     */
    private function multiThreadsExecute($userFunctions)
    {
        $this->resource->closeConnection(null);
        $threadNumber = 0;
        foreach ($userFunctions as $userFunction) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new LocalizedException(__('Unable to fork a new process'));
            } elseif ($pid) {
                $this->executeParentProcess($threadNumber);
            } else {
                $this->startChildProcess($userFunction);
            }
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        while (pcntl_waitpid(0, $status) != -1) {
            if ($status > 0) {
                $this->failInChildProcess = true;
            }
        }

        if ($this->failInChildProcess) {
            throw new LocalizedException(__('Fail in child process.'));
        }
    }

    private function executeParentProcess(int &$threadNumber)
    {
        $threadNumber++;
        if ($threadNumber >= $this->threadsCount) {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            pcntl_wait($status);
            if (pcntl_wexitstatus($status) !== 0) {
                // phpcs:enable
                $this->failInChildProcess = true;
            }
            $threadNumber--;
        }
    }

    private function startChildProcess(callable $userFunction)
    {
        try {
            call_user_func($userFunction);
        } catch (Throwable $e) {
            $this->logger()->crit('Child process failed with message: ' . $e->getMessage());
            $this->logger()->crit($e->getTraceAsString());
        } finally {
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit(0);
        }
    }

    private function singleThreadExecute(array $userFunctions)
    {
        foreach ($userFunctions as $userFunction) {
            call_user_func($userFunction);
        }
    }

    private function logger(): Zend_Log
    {
        return \YValiya\Core\Service\LoggerFactory::create()->get(self::ERROR_LOG_FILE_NAME);
    }
}
