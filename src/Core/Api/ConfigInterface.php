<?php

namespace YValiya\Core\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;

interface ConfigInterface
{
    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);

    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): bool;
}