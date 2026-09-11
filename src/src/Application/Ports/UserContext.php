<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface UserContext
{
    public function currentUserId(): ?int;

    public function requireUserId(): int;

    public function hasCapability(string $capability): bool;

    public function requireCapability(string $capability): void;
}
