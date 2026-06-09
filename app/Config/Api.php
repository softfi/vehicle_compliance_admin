<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Api extends BaseConfig
{
    /**
     * Bearer token lifetime in days.
     */
    public int $tokenTtlDays = 30;

    /**
     * Max active tokens per user (0 = unlimited).
     */
    public int $maxTokensPerUser = 5;
}
