<?php

declare(strict_types=1);

namespace SGFP\Domain\Enums;

enum CommitmentNature: string
{
    case ENTRADA = 'ENTRADA';
    case SAIDA = 'SAIDA';

    public static function fromLegacyType(CommitmentLegacyType $type): self
    {
        return match ($type) {
            CommitmentLegacyType::RECEITA => self::ENTRADA,
            CommitmentLegacyType::DESPESA => self::SAIDA,
        };
    }
}
