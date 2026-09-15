<?php

declare(strict_types=1);

namespace AuthCore\Authentication;

final class AuthenticationResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?array $account,
        public readonly ?string $errorCode,
    ) {
    }

    public static function success(array $account): self
    {
        return new self(true, $account, null);
    }

    public static function failure(string $errorCode = 'invalid_credentials'): self
    {
        return new self(false, null, $errorCode);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
