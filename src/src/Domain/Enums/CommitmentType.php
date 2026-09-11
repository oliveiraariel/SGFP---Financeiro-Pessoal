<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CommitmentType: string
{
    case RECEITA = 'RECEITA';
    case DESPESA = 'DESPESA';
}
