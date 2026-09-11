<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum EntryState: string
{
    case ATIVO = 'ATIVO';
    case DESFEITO = 'DESFEITO';
}
