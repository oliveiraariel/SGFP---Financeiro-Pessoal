<?php
declare(strict_types=1);
namespace SGFP\Application\Ports;

interface RestorationTokenStore
{
    public function store(int $userId, string $hash, int $expiresAt, string $metadata): void;
    /** @return array<string,mixed>|null */
    public function get(int $userId, string $hash): ?array;
}
