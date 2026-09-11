<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface UserPreferenceRepository
{
    public function get(string $key, int $userId): ?string;

    public function set(string $key, int $userId, string $value): void;
}
