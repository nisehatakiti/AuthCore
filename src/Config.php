<?php

declare(strict_types=1);

namespace AuthCore;

final class Config
{
    public const VERSION = AUTHCORE_VERSION;
    public const DB_VERSION = '1.1.0';
    public const OPTION_DB_VERSION = 'authcore_db_version';
    public const OPTION_SETTINGS = 'authcore_settings';
}
