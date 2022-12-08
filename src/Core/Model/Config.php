<?php

namespace YValiya\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use YValiya\Core\Api\ConfigInterface;

class Config implements ConfigInterface
{
    private ScopeConfigInterface $scopeConfig;
    private EncryptorInterface $encryptor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface   $encryptor
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function getValue(
        $path,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    )
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    public function isSetFlag(
        $path,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool
    {
        return $this->scopeConfig->isSetFlag($path, $scopeType, $scopeCode);
    }

    protected function decrypt(string $value): string
    {
        return $this->encryptor->decrypt($value);
    }
}