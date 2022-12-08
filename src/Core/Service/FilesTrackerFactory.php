<?php

namespace YValiya\Core\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

class FilesTrackerFactory
{
    public function create(string $directory, string $parentDirectory = DirectoryList::VAR_DIR): FilesTracker
    {
        return ObjectManager::getInstance()->create(FilesTracker::class, [
            "directory" => $parentDirectory,
            "subDirectory" => $directory
        ]);
    }
}
