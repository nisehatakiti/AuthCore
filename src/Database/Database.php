<?php

declare(strict_types=1);

namespace AuthCore\Database;

final class Database
{
    public static function table(string $name): string
    {
        return match ($name) {
            'applications' => Schema::applications_table(),
            'user_accounts' => Schema::user_accounts_table(),
            default => throw new \InvalidArgumentException('Unknown AuthCore table: ' . $name),
        };
    }
}
