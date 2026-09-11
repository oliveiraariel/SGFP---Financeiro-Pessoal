<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Commitment;
use SGFP\Infrastructure\Database\TableNames;

final class WpCommitmentRepository implements CommitmentRepository
{
    public function save(Commitment $commitment): Commitment
    {
        global $wpdb;

        $data = [
            'fk_id_usuario' => $commitment->userId,
            'fk_id_categoria' => $commitment->categoryId,
            'fk_id_recorrencia' => $commitment->recurrenceId,
            'nome' => $commitment->name,
            'valor' => $commitment->amount,
            'tipo' => $commitment->type->value,
            'natureza' => $commitment->nature->value,
            'mes_referencia' => $commitment->referenceMonth->format('Y-m-d'),
            'status' => $commitment->status->value,
        ];

        $format = ['%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s'];

        if ($commitment->id === null) {
            $result = $wpdb->insert(TableNames::commitment(), $data, $format);

            if ($result === false) {
                throw new \RuntimeException('Falha ao criar compromisso: ' . $wpdb->last_error);
            }

            return $commitment->withId((int) $wpdb->insert_id);
        }

        $wpdb->update(
            TableNames::commitment(),
            $data,
            ['id_compromisso' => $commitment->id],
            $format,
            ['%d']
        );

        return $commitment;
    }

    public function findById(int $id, int $userId): ?Commitment
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::commitment() . " WHERE id_compromisso = %d AND fk_id_usuario = %d",
            $id,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    public function findAllByUser(int $userId): array
    {
        global $wpdb;

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . TableNames::commitment() . " WHERE fk_id_usuario = %d ORDER BY mes_referencia DESC, criado_em DESC",
            $userId
        ), ARRAY_A);

        return array_map([$this, 'mapRow'], $rows ?: []);
    }

    public function findByRecurrenceIdAndMonth(int $recurrenceId, string $month, int $userId): ?Commitment
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::commitment()
            . " WHERE fk_id_recorrencia = %d AND mes_referencia = %s AND fk_id_usuario = %d",
            $recurrenceId,
            $month,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    public function findFirstByRecurrenceId(int $recurrenceId, int $userId): ?Commitment
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::commitment()
            . " WHERE fk_id_recorrencia = %d AND fk_id_usuario = %d ORDER BY mes_referencia ASC LIMIT 1",
            $recurrenceId,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $row): Commitment
    {
        return new Commitment(
            (int) $row['id_compromisso'],
            (int) $row['fk_id_usuario'],
            $row['fk_id_categoria'] !== null ? (int) $row['fk_id_categoria'] : null,
            $row['fk_id_recorrencia'] !== null ? (int) $row['fk_id_recorrencia'] : null,
            $row['nome'],
            (float) $row['valor'],
            CommitmentType::from($row['tipo']),
            CommitmentNature::from($row['natureza']),
            new \DateTimeImmutable($row['mes_referencia']),
            CommitmentStatus::from($row['status']),
            new \DateTimeImmutable($row['criado_em'])
        );
    }
}
