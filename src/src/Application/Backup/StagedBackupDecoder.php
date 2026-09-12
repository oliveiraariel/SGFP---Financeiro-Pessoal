<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;

final class StagedBackupDecoder
{
    private const SECTIONS = ['accounts', 'categories', 'recurrences', 'commitments', 'transfers', 'entries'];

    public function decode(array $payload, int $owner): StagedBackup
    {
        $this->keys($payload, ['metadata', 'theme', ...self::SECTIONS], 'top-level');
        $meta = $payload['metadata'];
        if (!is_array($meta)) throw new \InvalidArgumentException('Metadados do backup inválidos.');
        $this->keys($meta, ['schema', 'version', 'owner', 'origin', 'created_at'], 'metadata');
        if ($meta['schema'] !== 'sgfp-backup' || $meta['version'] !== 1 || $meta['owner'] !== $owner) {
            throw new \InvalidArgumentException('Backup incompatível com o usuário atual.');
        }
        if (!in_array($meta['origin'], ['manual', 'pre_restore'], true) || !is_string($meta['created_at']) || $meta['created_at'] === '') {
            throw new \InvalidArgumentException('Metadados do backup inválidos.');
        }
        if (!in_array($payload['theme'], ['light', 'dark'], true)) throw new \InvalidArgumentException('Tema do backup inválido.');
        foreach (self::SECTIONS as $section) {
            if (!is_array($payload[$section]) || array_is_list($payload[$section]) === false && $payload[$section] !== []) {
                // Records must be a JSON list; empty lists are valid.
                throw new \InvalidArgumentException('Seção de backup inválida: ' . $section . '.');
            }
            foreach ($payload[$section] as $record) $this->record($record, $owner, $section);
        }
        $this->references($payload);
        return new StagedBackup($meta, $payload['theme'], ...array_map(fn(string $s) => $payload[$s], self::SECTIONS));
    }

    private function keys(array $value, array $required, string $where): void
    {
        $actual = array_keys($value); sort($actual); $expected = $required; sort($expected);
        if ($actual !== $expected) throw new \InvalidArgumentException('Schema do backup inválido em ' . $where . '.');
    }

    private function record(mixed $record, int $owner, string $section): void
    {
        if (!is_array($record) || $record === [] || array_is_list($record)) throw new \InvalidArgumentException('Registro inválido em ' . $section . '.');
        foreach ($record as $value) if (is_array($value) || is_object($value) || is_resource($value)) throw new \InvalidArgumentException('Formato de registro inválido.');
        if (array_key_exists('userId', $record) && $record['userId'] !== $owner) throw new \InvalidArgumentException('Registro pertence a outro usuário.');
        if (array_key_exists('user_id', $record) && $record['user_id'] !== $owner) throw new \InvalidArgumentException('Registro pertence a outro usuário.');
        if (array_key_exists('id', $record) && $record['id'] !== null && (!is_int($record['id']) || $record['id'] <= 0)) throw new \InvalidArgumentException('Identificador inválido.');
        $enums = match ($section) {
            'accounts' => ['role' => AccountRole::class],
            'commitments' => ['type' => CommitmentType::class, 'nature' => CommitmentNature::class, 'status' => CommitmentStatus::class],
            'entries' => ['origin' => EntryOrigin::class, 'effectType' => EntryEffectType::class, 'state' => EntryState::class],
            default => [],
        };
        foreach ($enums as $field => $enum) if (!isset($record[$field]) || !is_string($record[$field]) || $enum::tryFrom($record[$field]) === null) throw new \InvalidArgumentException('Enum inválido em ' . $section . '.');
    }

    private function references(array $p): void
    {
        $ids = fn(string $s): array => array_filter(array_column($p[$s], 'id'), 'is_int');
        $account = array_flip($ids('accounts')); $category = array_flip($ids('categories')); $recurrence = array_flip($ids('recurrences')); $commitment = array_flip($ids('commitments'));
        foreach ($p['commitments'] as $r) foreach (['categoryId' => $category, 'recurrenceId' => $recurrence] as $field => $set) if (($r[$field] ?? null) !== null && !isset($set[$r[$field]])) throw new \InvalidArgumentException('Referência interna inexistente.');
        foreach ($p['entries'] as $r) foreach (['accountId' => $account, 'commitmentId' => $commitment] as $field => $set) if (!isset($set[$r[$field] ?? null])) throw new \InvalidArgumentException('Referência interna inexistente.');
        foreach ($p['transfers'] as $r) foreach (['sourceAccountId' => $account, 'targetAccountId' => $account, 'commitmentId' => $commitment] as $field => $set) if (!isset($set[$r[$field] ?? null])) throw new \InvalidArgumentException('Referência interna inexistente.');
    }
}
