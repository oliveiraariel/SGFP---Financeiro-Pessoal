<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Domain\Models\Recurrence;
use SGFP\Infrastructure\Database\TableNames;

final class WpRecurrenceRepository implements RecurrenceRepository
{
    public function save(Recurrence $recurrence): Recurrence
    {
        global $wpdb;

        $data = [
            'fk_id_usuario' => $recurrence->userId,
            'inicio_mes' => $recurrence->startsIn->format('Y-m-d'),
            'quantidade_meses' => $recurrence->monthsCount,
            'encerrada_no_mes' => $recurrence->endedIn?->format('Y-m-d'),
        ];

        $format = ['%d', '%s', '%d', '%s'];

        if ($recurrence->id === null) {
            $result = $wpdb->insert(TableNames::recurrence(), $data, $format);

            if ($result === false) {
                throw new \RuntimeException('Falha ao criar recorrência: ' . $wpdb->last_error);
            }

            return $recurrence->withId((int) $wpdb->insert_id);
        }

        $wpdb->update(
            TableNames::recurrence(),
            $data,
            ['id_recorrencia' => $recurrence->id],
            $format,
            ['%d']
        );

        return $recurrence;
    }

    public function findById(int $id, int $userId): ?Recurrence
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::recurrence() . " WHERE id_recorrencia = %d AND fk_id_usuario = %d",
            $id,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $row): Recurrence
    {
        return new Recurrence(
            (int) $row['id_recorrencia'],
            (int) $row['fk_id_usuario'],
            new \DateTimeImmutable($row['inicio_mes']),
            $row['quantidade_meses'] !== null ? (int) $row['quantidade_meses'] : null,
            $row['encerrada_no_mes'] !== null ? new \DateTimeImmutable($row['encerrada_no_mes']) : null,
            new \DateTimeImmutable($row['criada_em'])
        );
    }
}
