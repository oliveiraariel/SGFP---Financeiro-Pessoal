<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;

final class StagedBackupDecoder
{
    private const SECTIONS = ['accounts', 'categories', 'recurrences', 'commitments', 'entries'];

    public function decode(array $payload, int $owner): StagedBackup
    {
        $this->keys($payload, ['metadata', 'theme', ...self::SECTIONS], 'top-level');

        $meta = $payload['metadata'];
        if (!is_array($meta)) {
            throw new \InvalidArgumentException('Metadados do backup inválidos.');
        }

        $this->keys($meta, ['schema', 'version', 'owner', 'origin', 'created_at'], 'metadata');

        if ($meta['schema'] !== 'sgfp-backup'
            || $meta['version'] !== 2
            || $meta['owner'] !== $owner) {
            throw new \InvalidArgumentException('Backup incompatível com o usuário atual.');
        }

        if (!in_array($meta['origin'], ['manual', 'pre_restore'], true)
            || !is_string($meta['created_at'])
            || $meta['created_at'] === '') {
            throw new \InvalidArgumentException('Metadados do backup inválidos.');
        }

        if (!in_array($payload['theme'], ['light', 'dark'], true)) {
            throw new \InvalidArgumentException('Tema do backup inválido.');
        }

        foreach (self::SECTIONS as $section) {
            if (!is_array($payload[$section])
                || (array_is_list($payload[$section]) === false && $payload[$section] !== [])) {
                throw new \InvalidArgumentException('Seção de backup inválida: ' . $section . '.');
            }

            foreach ($payload[$section] as $record) {
                $this->record($record, $owner, $section);
            }
        }

        if (count($payload['accounts']) !== 1) {
            throw new \InvalidArgumentException('O backup deve conter exatamente uma Conta Financeira.');
        }

        $this->references($payload);

        return new StagedBackup(
            $meta,
            $payload['theme'],
            $payload['accounts'],
            $payload['categories'],
            $payload['recurrences'],
            $payload['commitments'],
            $payload['entries'],
        );
    }

    private function keys(array $value, array $required, string $where): void
    {
        $actual = array_keys($value);
        sort($actual);
        $expected = $required;
        sort($expected);

        if ($actual !== $expected) {
            throw new \InvalidArgumentException('Schema do backup inválido em ' . $where . '.');
        }
    }

    private function record(mixed $record, int $owner, string $section): void
    {
        if (!is_array($record) || $record === [] || array_is_list($record)) {
            throw new \InvalidArgumentException('Registro inválido em ' . $section . '.');
        }

        foreach ($record as $value) {
            if (is_array($value) || is_object($value) || is_resource($value)) {
                throw new \InvalidArgumentException('Formato de registro inválido.');
            }
        }

        if (($record['userId'] ?? $owner) !== $owner) {
            throw new \InvalidArgumentException('Registro pertence a outro usuário.');
        }

        if (array_key_exists('id', $record)
            && $record['id'] !== null
            && (!is_int($record['id']) || $record['id'] <= 0)) {
            throw new \InvalidArgumentException('Identificador inválido.');
        }

        $enums = match ($section) {
            'commitments' => [
                'nature' => CommitmentNature::class,
                'status' => CommitmentStatus::class,
            ],
            'entries' => [
                'origin' => EntryOrigin::class,
                'effectType' => EntryEffectType::class,
                'state' => EntryState::class,
            ],
            default => [],
        };

        foreach ($enums as $field => $enum) {
            if (!isset($record[$field])
                || !is_string($record[$field])
                || $enum::tryFrom($record[$field]) === null) {
                throw new \InvalidArgumentException('Enum inválido em ' . $section . '.');
            }
        }
    }

    private function references(array $payload): void
    {
        $ids = static fn (string $section): array =>
            array_flip(array_values(array_filter(
                array_column($payload[$section], 'id'),
                static fn (mixed $value): bool => is_int($value)
            )));

        $accounts = $ids('accounts');
        $categories = $ids('categories');
        $recurrences = $ids('recurrences');
        $commitments = $ids('commitments');

        foreach ($payload['commitments'] as $record) {
            $this->optionalReference($record['categoryId'] ?? null, $categories);
            $this->optionalReference($record['recurrenceId'] ?? null, $recurrences);
        }

        foreach ($payload['entries'] as $record) {
            $this->requiredReference($record['accountId'] ?? null, $accounts);
            $this->optionalReference($record['commitmentId'] ?? null, $commitments);
        }
    }

    private function requiredReference(mixed $value, array $set): void
    {
        if (!is_int($value) || !isset($set[$value])) {
            throw new \InvalidArgumentException('Referência interna inexistente.');
        }
    }

    private function optionalReference(mixed $value, array $set): void
    {
        if ($value === null) {
            return;
        }

        $this->requiredReference($value, $set);
    }
}
