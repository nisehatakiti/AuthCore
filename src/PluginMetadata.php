<?php

declare(strict_types=1);

namespace AuthCore;

final class PluginMetadata
{
    public function __construct(
        public readonly string $type,
        public readonly string $applicationKey,
        public readonly string $applicationName,
        public readonly ?string $parentApplication,
        public readonly ?string $version,
        public readonly ?string $uri,
        public readonly ?string $vendor,
        public readonly ?string $vendorUri,
        public readonly ?string $description,
        public readonly ?string $icon,
        public readonly ?string $extensionType,
    ) {
    }

    /**
     * Read AuthCore metadata from a WordPress plugin main file.
     *
     * @throws AuthCoreException When required metadata is missing or invalid.
     */
    public static function fromFile(string $pluginFile): self
    {
        if (!function_exists('get_file_data')) {
            throw new AuthCoreException('WordPress get_file_data() is not available.');
        }

        $headers = [
            'type' => 'AuthCore',
            'application_key' => 'AuthCore Application Key',
            'application_name' => 'AuthCore Application Name',
            'parent_application' => 'AuthCore Parent Application',
            'version' => 'AuthCore Application Version',
            'uri' => 'AuthCore Application URI',
            'vendor' => 'AuthCore Vendor',
            'vendor_uri' => 'AuthCore Vendor URI',
            'description' => 'AuthCore Description',
            'icon' => 'AuthCore Icon',
            'extension_type' => 'AuthCore Extension Type',
        ];

        $data = get_file_data($pluginFile, $headers, 'plugin');
        $type = strtolower(trim((string) ($data['type'] ?? '')));
        $key = trim((string) ($data['application_key'] ?? ''));
        $name = trim((string) ($data['application_name'] ?? ''));

        if (!in_array($type, ['application', 'extension'], true)) {
            throw new AuthCoreException('Invalid or missing AuthCore plugin type.');
        }

        if ($key === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $key)) {
            throw new AuthCoreException('Invalid or missing AuthCore Application Key.');
        }

        if ($name === '') {
            throw new AuthCoreException('Missing AuthCore Application Name.');
        }

        $parent = trim((string) ($data['parent_application'] ?? ''));

        if ($type === 'application' && $parent !== '') {
            throw new AuthCoreException('Application must not define AuthCore Parent Application.');
        }

        if ($type === 'extension' && ($parent === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $parent))) {
            throw new AuthCoreException('Extension requires a valid AuthCore Parent Application.');
        }

        return new self(
            type: $type,
            applicationKey: $key,
            applicationName: $name,
            parentApplication: $parent !== '' ? $parent : null,
            version: self::nullable($data['version'] ?? null),
            uri: self::nullable($data['uri'] ?? null),
            vendor: self::nullable($data['vendor'] ?? null),
            vendorUri: self::nullable($data['vendor_uri'] ?? null),
            description: self::nullable($data['description'] ?? null),
            icon: self::nullable($data['icon'] ?? null),
            extensionType: self::nullable($data['extension_type'] ?? null),
        );
    }

    private static function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
