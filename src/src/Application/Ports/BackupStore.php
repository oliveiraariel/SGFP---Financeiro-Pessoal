<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface BackupStore
{
    /** @return array{path:string,hash:string,expires_at:int,origin:string} */
    public function persist(int $userId, string $content, string $origin, int $expiresAt): array;
}
