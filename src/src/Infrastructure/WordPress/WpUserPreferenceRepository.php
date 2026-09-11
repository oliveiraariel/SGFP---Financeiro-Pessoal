<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\UserPreferenceRepository;

final class WpUserPreferenceRepository implements UserPreferenceRepository
{
    private const META_PREFIX = 'sgfp_';

    public function get(string $key, int $userId): ?string
    {
        $value = get_user_meta($userId, self::META_PREFIX . $key, true);
        return is_string($value) && $value !== '' ? $value : null;
    }

    public function set(string $key, int $userId, string $value): void
    {
        update_user_meta($userId, self::META_PREFIX . $key, $value);
    }
}
