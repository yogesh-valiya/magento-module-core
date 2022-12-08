<?php

namespace YValiya\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class UtilsHelper extends AbstractHelper
{
    public function parseDomainFromURL(string $url): string
    {
        $parsed_url = parse_url($url);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = $parsed_url['host'] ?? '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        return "$scheme$host$port";
    }
}