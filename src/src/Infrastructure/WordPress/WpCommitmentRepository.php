<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\CommitmentRepository;
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
            'fk_id_conta' => $commitment->accountId,
            'fk_id_categoria' => $commitment->categoryId,
            'descricao' => $commitment->description,
            'valor' => $commitment->amount,
            'tipo' => $commitment->type->value,
            'status' => $commitment->status->value,
            'data_vencimento' => $commitment->dueDate->format('Y-m-d'),
            'efetivado_em' => $commitment->settledAt?->format('Y-m-d H:i:s'),
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
            "SELECT * FROM " . TableNames::commitment() . " WHERE fk_id_usuario = %d ORDER BY data_vencimento DESC, criado_em DESC",
            $userId
        ), ARRAY_A);

        return array_map([$this, 'mapRow'], $rows ?: []);
    }

    private function mapRow(array $row): Commitment
    {
        return new Commitment(
            (int) $row['id_compromisso'],
            (int) $row['fk_id_usuario'],
            (int) $row['fk_id_conta'],
            $row['fk_id_categoria'] !== null ? (int) $row['fk_id_categoria'] : null,
            $row['descricao'],
            (float) $row['valor'],
            CommitmentType::from($row['tipo']),
            CommitmentStatus::from($row['status']),
            new \DateTimeImmutable($row['data_vencimento']),
            $row['efetivado_em'] !== null ? new \DateTimeImmutable($row['efetivado_em']) : null,
            new \DateTimeImmutable($row['criado_em'])
        );
    }
}
