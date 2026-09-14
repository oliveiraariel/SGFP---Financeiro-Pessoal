<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\UserDataPurger;
use SGFP\Infrastructure\Database\TableNames;

final class WpUserDataPurger implements UserDataPurger
{
    public function purgeSgfpData(int $userId): void
    {
        global $wpdb;

        if ($userId <= 0) {
            throw new \InvalidArgumentException('Usuário inválido para limpeza.');
        }

        $steps = [
            [TableNames::restorationToken(), 'user_id'],
            [TableNames::entry(), 'fk_id_usuario'],
            [TableNames::commitment(), 'fk_id_usuario'],
            [TableNames::recurrence(), 'fk_id_usuario'],
            [TableNames::category(), 'fk_id_usuario'],
            [TableNames::account(), 'fk_id_usuario'],
        ];

        foreach ($steps as [$table, $column]) {
            $result = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table} WHERE {$column} = %d",
                $userId
            ));

            if ($result === false) {
                throw new \RuntimeException(
                    'Falha ao limpar dados SGFP: ' . $wpdb->last_error
                );
            }
        }

        $pattern = $wpdb->esc_like('sgfp_') . '%';
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s",
            $userId,
            $pattern
        ));

        if ($result === false) {
            throw new \RuntimeException(
                'Falha ao limpar preferências SGFP: ' . $wpdb->last_error
            );
        }
    }
}
