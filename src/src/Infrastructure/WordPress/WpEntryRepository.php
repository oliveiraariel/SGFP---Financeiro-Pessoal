<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\EntryRepository;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Entry;
use SGFP\Infrastructure\Database\TableNames;

final class WpEntryRepository implements EntryRepository
{
    public function save(Entry $entry): Entry
    {
        global $wpdb;

        $data = [
            'fk_id_usuario' => $entry->userId,
            'fk_id_conta' => $entry->accountId,
            'fk_id_compromisso' => $entry->commitmentId,
            'fk_id_transferencia' => $entry->transferId,
            'descricao' => $entry->description,
            'valor' => $entry->amount,
            'tipo' => $entry->type->value,
            'data_competencia' => $entry->competenceDate->format('Y-m-d'),
            'efetivado_em' => $entry->settledAt->format('Y-m-d H:i:s'),
        ];

        $format = ['%d', '%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s'];

        if ($entry->id === null) {
            $result = $wpdb->insert(TableNames::entry(), $data, $format);

            if ($result === false) {
                throw new \RuntimeException('Falha ao criar lançamento: ' . $wpdb->last_error);
            }

            return $entry->withId((int) $wpdb->insert_id);
        }

        $wpdb->update(
            TableNames::entry(),
            $data,
            ['id_lancamento' => $entry->id],
            $format,
            ['%d']
        );

        return $entry;
    }

    public function findByCommitmentId(int $commitmentId, int $userId): ?Entry
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::entry() . " WHERE fk_id_compromisso = %d AND fk_id_usuario = %d",
            $commitmentId,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $row): Entry
    {
        return new Entry(
            (int) $row['id_lancamento'],
            (int) $row['fk_id_usuario'],
            (int) $row['fk_id_conta'],
            $row['fk_id_compromisso'] !== null ? (int) $row['fk_id_compromisso'] : null,
            $row['fk_id_transferencia'] !== null ? (int) $row['fk_id_transferencia'] : null,
            $row['descricao'],
            (float) $row['valor'],
            CommitmentType::from($row['tipo']),
            new \DateTimeImmutable($row['data_competencia']),
            new \DateTimeImmutable($row['efetivado_em']),
            new \DateTimeImmutable($row['criado_em'])
        );
    }
}
