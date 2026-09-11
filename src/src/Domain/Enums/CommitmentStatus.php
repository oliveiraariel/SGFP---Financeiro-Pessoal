<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CommitmentStatus: string
{
    case PENDENTE = 'PENDENTE';
    case EFETIVADO = 'EFETIVADO';
    case CANCELADO = 'CANCELADO';
}
