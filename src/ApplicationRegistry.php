<?php

declare(strict_types=1);

namespace AuthCore;

use AuthCore\Database\ApplicationRepository;

final class ApplicationRegistry
{
    private ApplicationRepository $repository;

    public function __construct(?ApplicationRepository $repository = null)
    {
        $this->repository = $repository ?? new ApplicationRepository();
    }

    public function registerFromFile(string $pluginFile): int
    {
        $metadata = PluginMetadata::fromFile($pluginFile);
        $existing = $this->repository->findByKey($metadata->applicationKey);

        if ($metadata->type === 'extension') {
            $parent = $this->repository->findByKey((string) $metadata->parentApplication);
            if ($parent === null) {
                throw new AuthCoreException(
                    sprintf(
                        'Cannot register extension "%s": parent application "%s" is not registered.',
                        $metadata->applicationKey,
                        $metadata->parentApplication
                    )
                );
            }

            if (($parent['type'] ?? '') !== 'application') {
                throw new AuthCoreException(
                    sprintf('Extension parent "%s" must be an application.', $metadata->parentApplication)
                );
            }

            $parentId = (int) $parent['application_id'];
        } else {
            $parentId = null;
        }

        if ($existing !== null) {
            if (($existing['type'] ?? '') !== $metadata->type) {
                throw new AuthCoreException(
                    sprintf('AuthCore Application Key "%s" is already registered with another type.', $metadata->applicationKey)
                );
            }

            $this->repository->updateFromMetadata((int) $existing['application_id'], $metadata, $parentId);
            return (int) $existing['application_id'];
        }

        return $this->repository->insert($metadata, $parentId);
    }

    public function findByKey(string $applicationKey): ?array
    {
        return $this->repository->findByKey($applicationKey);
    }

    public function findById(int $applicationId): ?array
    {
        return $this->repository->findById($applicationId);
    }
}
