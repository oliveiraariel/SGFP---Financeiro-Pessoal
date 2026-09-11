<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CommitmentType: string
{
    case PADRAO = 'PADRAO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
}
