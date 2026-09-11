<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CategoryType: string
{
    case RECEITA = 'RECEITA';
    case DESPESA = 'DESPESA';
}
