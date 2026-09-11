<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum AccountRole: string
{
    case PRINCIPAL = 'PRINCIPAL';
    case SECUNDARIA = 'SECUNDARIA';

    public function label(): string
    {
        return match ($this) {
            self::PRINCIPAL => 'Principal',
            self::SECUNDARIA => 'Secundária',
        };
    }
}
