<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum EntryEffectType: string
{
    case ENTRADA = 'ENTRADA';
    case SAIDA = 'SAIDA';

    public static function fromCommitmentNature(CommitmentNature $nature): self
    {
        return match ($nature) {
            CommitmentNature::ENTRADA => self::ENTRADA,
            CommitmentNature::SAIDA => self::SAIDA,
        };
    }
}
