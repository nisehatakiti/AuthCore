<?php

declare(strict_types=1);

namespace AuthCore;

final class AuthCore
{
    public static function version(): string
    {
        return AUTHCORE_VERSION;
    }

    public static function isLoaded(): bool
    {
        return did_action('authcore/loaded') > 0;
    }
}
