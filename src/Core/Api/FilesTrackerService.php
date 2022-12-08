<?php

namespace YValiya\Core\Api;

interface FilesTrackerService
{
    public function getCurrentFilesList(): array;

    public function getOldFilesList(): array;

    public function getNewFilesList(): array;

    public function addNewFileRecord(string $fileName): void;

    public function updateWithLatestFiles(): void;
}