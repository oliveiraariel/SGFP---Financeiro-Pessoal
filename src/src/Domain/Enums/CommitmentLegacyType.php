<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CommitmentLegacyType: string
{
    case RECEITA = 'RECEITA';
    case DESPESA = 'DESPESA';

    public static function fromNature(CommitmentNature $nature): self
    {
        return match ($nature) {
            CommitmentNature::ENTRADA => self::RECEITA,
            CommitmentNature::SAIDA => self::DESPESA,
        };
    }
}
