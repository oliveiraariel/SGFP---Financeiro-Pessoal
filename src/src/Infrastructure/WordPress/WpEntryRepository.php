<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\EntryRepository;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Entry;
use SGFP\Infrastructure\Database\TableNames;

final class WpEntryRepository implements EntryRepository
{
    public function save(Entry $entry): Entry
    {
        global $wpdb;

        $data = [
            'fk_id_usuario' => $entry->userId,
            'fk_id_compromisso' => $entry->commitmentId,
            'fk_id_conta' => $entry->accountId,
            'origem' => $entry->origin->value,
            'nome' => $entry->name,
            'valor' => $entry->amount,
            'tipo_efeito' => $entry->effectType->value,
            'data_efetivacao' => $entry->settledAt->format('Y-m-d H:i:s'),
            'descricao' => $entry->description,
            'estado' => $entry->state->value,
            'desfeito_em' => $entry->undoneAt?->format('Y-m-d H:i:s'),
        ];

        $format = ['%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s'];

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

    public function findActiveInitialBalanceByAccount(int $accountId, int $userId): ?Entry
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::entry()
            . " WHERE fk_id_conta = %d AND fk_id_usuario = %d AND origem = 'SALDO_INICIAL' AND estado = 'ATIVO'",
            $accountId,
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
            EntryOrigin::from($row['origem']),
            $row['nome'],
            (float) $row['valor'],
            EntryEffectType::from($row['tipo_efeito']),
            new \DateTimeImmutable($row['data_efetivacao']),
            $row['descricao'],
            EntryState::from($row['estado']),
            new \DateTimeImmutable($row['criado_em']),
            $row['desfeito_em'] !== null ? new \DateTimeImmutable($row['desfeito_em']) : null
        );
    }
}
