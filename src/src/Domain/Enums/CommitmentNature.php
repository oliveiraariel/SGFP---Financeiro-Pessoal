<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CommitmentNature: string
{
    case ENTRADA = 'ENTRADA';
    case SAIDA = 'SAIDA';
}
