<?php

namespace YValiya\Core\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\FlagManager;
use YValiya\Core\Api\FilesTrackerService;

class FilesTracker implements FilesTrackerService
{
    const FLAG_CODE = "yvaliya_file_track";

    private FlagManager $flagManager;
    private DirectoryList $directoryList;
    private File $driverFile;
    private string $parentDirectory;
    private string $directory;

    /**
     * @throws FileSystemException
     */
    public function __construct(
        File          $driverFile,
        DirectoryList $directoryList,
        FlagManager   $flagManager,
        string        $directory,
        string        $subDirectory
    )
    {
        $this->flagManager = $flagManager;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->parentDirectory = $directory;
        $this->directory = $subDirectory;

        $this->createDirectoryIfNotExists();
    }

    /**
     * @throws FileSystemException
     */
    public function getCurrentFilesList(): array
    {
        $csvDirectory = $this->getDirectoryPath();
        $files = $this->driverFile->readDirectory($csvDirectory);
        $result = [];

        foreach ($files as $file) {
            if ($this->driverFile->isFile($file)) {
                $result[] = pathinfo($file)['basename'];
            }
        }

        return $result;
    }

    public function getOldFilesList(): array
    {
        $flagCode = $this->getFlagCode();
        $result = $this->flagManager->getFlagData($flagCode);
        return is_array($result) ? $result : [];
    }

    /**
     * @throws FileSystemException
     */
    public function getNewFilesList(): array
    {
        $oldFiles = $this->getOldFilesList();
        $currentFiles = $this->getCurrentFilesList();
        return array_diff($currentFiles, $oldFiles);
    }

    /**
     * @throws FileSystemException
     */
    public function addNewFileRecord(string $fileName): void
    {
        $this->cleanRecords();
        $oldFiles = $this->getOldFilesList();
        $oldFiles[] = $fileName;
        $this->flagManager->saveFlag($this->getFlagCode(), $oldFiles);
    }

    /**
     * @throws FileSystemException
     */
    public function updateWithLatestFiles(): void
    {
        $currentFiles = $this->getCurrentFilesList();
        $this->flagManager->saveFlag($this->getFlagCode(), $currentFiles);
    }

    /**
     * @throws FileSystemException
     */
    protected function cleanRecords()
    {
        $currentFiles = $this->getCurrentFilesList();
        $oldFiles = $this->getOldFilesList();
        $newRecord = array_intersect($currentFiles, $oldFiles);
        $this->flagManager->saveFlag($this->getFlagCode(), $newRecord);
    }

    /**
     * @throws FileSystemException
     */
    protected function getDirectoryPath(): string
    {
        return $this->directoryList->getPath($this->parentDirectory) . $this->directory;
    }

    /**
     * @throws FileSystemException
     */
    protected function getAbsoluteFilePath(string $fileName): string
    {
        return $this->getDirectoryPath() . DIRECTORY_SEPARATOR . $fileName;
    }

    protected function getFlagCode(): string
    {
        return self::FLAG_CODE . '_' . str_replace('/', '_', $this->parentDirectory)
            . str_replace('/', '_', $this->directory);
    }

    /**
     * @throws FileSystemException
     */
    protected function createDirectoryIfNotExists()
    {
        $path = $this->getDirectoryPath();
        if (!$this->driverFile->isExists($path)) {
            $this->driverFile->createDirectory($path);
        }
    }
}
