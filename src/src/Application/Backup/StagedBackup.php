<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

final class StagedBackup
{
    public function __construct(
        public readonly array $metadata,
        public readonly string $theme,
        public readonly array $accounts,
        public readonly array $categories,
        public readonly array $recurrences,
        public readonly array $commitments,
        public readonly array $entries,
    ) {}

    public function counts(): array
    {
        return [
            'accounts' => count($this->accounts),
            'categories' => count($this->categories),
            'recurrences' => count($this->recurrences),
            'commitments' => count($this->commitments),
            'entries' => count($this->entries),
        ];
    }
}
