<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Contact extends BaseConfig
{
    /** @var list<string> */
    public array $blockedEmails = [];

    /** @var list<string> */
    public array $blockedDomains = [];

    /** @var list<string> */
    public array $blockedIpAddresses = [];

    public int $dailyLimitPerIp    = 20;
    public int $dailyLimitPerEmail = 20;

    public static function fromEnv(): static
    {
        $config = new static();

        $config->blockedEmails = array_filter(array_map('trim', explode(',', getenv('CONTACT_BLOCKED_EMAILS') ?: '')));
        $config->blockedDomains = array_filter(array_map('trim', explode(',', getenv('CONTACT_BLOCKED_DOMAINS') ?: '')));
        $config->blockedIpAddresses = array_filter(array_map('trim', explode(',', getenv('CONTACT_BLOCKED_IPS') ?: '')));

        $config->dailyLimitPerIp = (int) (getenv('CONTACT_LIMIT_PER_IP') ?: $config->dailyLimitPerIp);
        $config->dailyLimitPerEmail = (int) (getenv('CONTACT_LIMIT_PER_EMAIL') ?: $config->dailyLimitPerEmail);

        return $config;
    }
}
