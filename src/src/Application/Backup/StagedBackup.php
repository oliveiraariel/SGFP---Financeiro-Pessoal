<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

/** Validated, immutable input to a future restoration operation. */
final class StagedBackup
{
    public function __construct(
        public readonly array $metadata,
        public readonly string $theme,
        public readonly array $accounts,
        public readonly array $categories,
        public readonly array $recurrences,
        public readonly array $commitments,
        public readonly array $transfers,
        public readonly array $entries,
    ) {}

    public function counts(): array
    {
        return array_map('count', [
            'accounts' => $this->accounts, 'categories' => $this->categories,
            'recurrences' => $this->recurrences, 'commitments' => $this->commitments,
            'transfers' => $this->transfers, 'entries' => $this->entries,
        ]);
    }
}
