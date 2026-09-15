<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface UserDataPurger
{
    public function purgeSgfpData(int $userId): void;
}
