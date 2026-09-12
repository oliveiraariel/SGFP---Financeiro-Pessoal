<?php
declare(strict_types=1);
namespace SGFP\Application\Backup;

/** Immutable, persistence-free description of a validated restoration import. */
final class RestorationImportPlan
{
    public function __construct(
        public readonly int $userId,
        public readonly array $replacementOrder,
        public readonly array $records,
        public readonly array $referenceRemapping,
        public readonly string $theme,
    ) {}

    public function counts(): array { return array_map('count', $this->records); }
}
