<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface RestorationTokenClaim
{
    /** @return array<string,mixed>|null */
    public function claim(int $userId, string $token, int $now): ?array;
}
