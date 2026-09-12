<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\TransferRepository;
use SGFP\Domain\Models\Transfer;
use SGFP\Infrastructure\Database\TableNames;

final class WpTransferRepository implements TransferRepository
{
    public function save(Transfer $transfer): void
    {
        global $wpdb;

        $result = $wpdb->replace(
            TableNames::transfer(),
            [
                'fk_id_compromisso' => $transfer->commitmentId,
                'fk_id_usuario' => $transfer->userId,
                'fk_id_conta_origem' => $transfer->sourceAccountId,
                'fk_id_conta_destino' => $transfer->targetAccountId,
            ],
            ['%d', '%d', '%d', '%d']
        );

        if ($result === false) {
            throw new \RuntimeException('Falha ao persistir transferência: ' . $wpdb->last_error);
        }
    }

    public function findByCommitmentId(int $commitmentId, int $userId): ?Transfer
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::transfer() . " WHERE fk_id_compromisso = %d AND fk_id_usuario = %d",
            $commitmentId,
            $userId
        ), ARRAY_A);

        if ($row === null) {
            return null;
        }

        return new Transfer(
            (int) $row['fk_id_compromisso'],
            (int) $row['fk_id_usuario'],
            (int) $row['fk_id_conta_origem'],
            (int) $row['fk_id_conta_destino'],
        );
    }

    public function findAllByUser(int $userId): array
    {
        global $wpdb;

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . TableNames::transfer()
            . " WHERE fk_id_usuario = %d ORDER BY fk_id_compromisso ASC",
            $userId
        ), ARRAY_A);

        return array_map(static fn (array $row): Transfer => new Transfer(
            (int) $row['fk_id_compromisso'],
            (int) $row['fk_id_usuario'],
            (int) $row['fk_id_conta_origem'],
            (int) $row['fk_id_conta_destino'],
        ), $rows ?: []);
    }
}
