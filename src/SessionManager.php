<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Authentication\AuthenticationResult;
use AuthCore\Database\SessionRepository;

final class SessionManager
{
    private const COOKIE_PREFIX = 'authcore_session_';
    private const TOKEN_BYTES = 32;
    private const LIFETIME = 1209600; // 14 days.

    public function __construct(
        private readonly SessionRepository $repository = new SessionRepository(),
        private readonly ApplicationRegistry $applications = new ApplicationRegistry(),
        private readonly UserAccountManager $accounts = new UserAccountManager(),
    ) {
    }

    public function login(int $applicationId, string $identifier, string $password): AuthenticationResult
    {
        $contextId = $this->resolveApplicationContext($applicationId);
        $result = (new Authentication\Authenticator())->authenticate($contextId, $identifier, $password);

        if (!$result->isSuccess() || !is_array($result->account) || !isset($result->account['user_account_id'])) {
            return $result;
        }

        $this->logout($contextId);

        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $now = gmdate('Y-m-d H:i:s');
        $expiresAt = gmdate('Y-m-d H:i:s', time() + self::LIFETIME);

        $this->repository->create(
            $contextId,
            (int) $result->account['user_account_id'],
            hash('sha256', $token),
            $now,
            $expiresAt
        );

        $this->setCookie($contextId, $token, time() + self::LIFETIME);

        return $result;
    }

    public function logout(int $applicationId): void
    {
        $contextId = $this->resolveApplicationContext($applicationId);
        $token = $this->readCookie($contextId);

        if ($token !== null) {
            $this->repository->deleteByTokenHash($contextId, hash('sha256', $token));
        }

        $this->clearCookie($contextId);
    }

    public function getCurrentAccount(int $applicationId): ?array
    {
        $contextId = $this->resolveApplicationContext($applicationId);
        $token = $this->readCookie($contextId);

        if ($token === null) {
            return null;
        }

        $now = gmdate('Y-m-d H:i:s');
        $this->repository->deleteExpired($now);
        $session = $this->repository->findValidByTokenHash($contextId, hash('sha256', $token), $now);

        if ($session === null) {
            $this->clearCookie($contextId);
            return null;
        }

        $account = $this->accounts->get($contextId, (int) $session['user_account_id']);
        if (!is_array($account) || ($account['status'] ?? '') !== 'active') {
            $this->repository->deleteByTokenHash($contextId, hash('sha256', $token));
            $this->clearCookie($contextId);
            return null;
        }

        $this->repository->touch((int) $session['session_id'], $contextId, $now);
        unset($account['password_hash']);
        return $account;
    }

    public function resolveApplicationContext(int $applicationId): int
    {
        $application = $this->applications->findById($applicationId);
        if ($application === null || ($application['status'] ?? '') !== 'active') {
            throw new AuthCoreException('Invalid or inactive AuthCore application.');
        }

        if (($application['type'] ?? 'application') === 'extension') {
            $parentId = (int) ($application['parent_application_id'] ?? 0);
            $parent = $parentId > 0 ? $this->applications->findById($parentId) : null;
            if ($parent === null || ($parent['type'] ?? '') !== 'application' || ($parent['status'] ?? '') !== 'active') {
                throw new AuthCoreException('Invalid or inactive parent application.');
            }
            return $parentId;
        }

        return $applicationId;
    }

    private function cookieName(int $applicationId): string
    {
        return self::COOKIE_PREFIX . $applicationId;
    }

    private function readCookie(int $applicationId): ?string
    {
        $name = $this->cookieName($applicationId);
        if (!isset($_COOKIE[$name]) || !is_string($_COOKIE[$name])) {
            return null;
        }

        $token = trim($_COOKIE[$name]);
        return preg_match('/^[a-f0-9]{64}$/', $token) === 1 ? $token : null;
    }

    private function setCookie(int $applicationId, string $token, int $expires): void
    {
        $this->sendCookie($this->cookieName($applicationId), $token, $expires);
    }

    private function clearCookie(int $applicationId): void
    {
        $this->sendCookie($this->cookieName($applicationId), '', time() - 3600);
    }

    private function sendCookie(string $name, string $value, int $expires): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie($name, $value, [
            'expires' => $expires,
            'path' => COOKIEPATH ?: '/',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
