<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface UserIdentityDeleter
{
    public function delete(int $userId): void;
}
