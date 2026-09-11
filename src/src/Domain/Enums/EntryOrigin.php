<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum EntryOrigin: string
{
    case COMPROMISSO = 'COMPROMISSO';
    case SALDO_INICIAL = 'SALDO_INICIAL';
}
