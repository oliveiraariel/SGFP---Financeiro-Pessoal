<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface UserOperationLock
{
    public function acquire(int $userId): void;
    public function release(int $userId): void;
}
