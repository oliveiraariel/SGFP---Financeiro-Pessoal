<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Models\Account;
use SGFP\Infrastructure\Database\TableNames;

final class WpAccountRepository implements AccountRepository
{
    public function save(Account $account): Account
    {
        global $wpdb;

        $data = [
            'fk_id_usuario' => $account->userId,
            'nome' => $account->name,
            'papel' => $account->role->value,
        ];

        $format = ['%d', '%s', '%s'];

        if ($account->id === null) {
            $result = $wpdb->insert(TableNames::account(), $data, $format);

            if ($result === false) {
                throw new \RuntimeException('Falha ao criar conta: ' . $wpdb->last_error);
            }

            return $account->withId((int) $wpdb->insert_id);
        }

        $wpdb->update(
            TableNames::account(),
            $data,
            ['id_conta' => $account->id],
            $format,
            ['%d']
        );

        return $account;
    }

    public function findById(int $id, int $userId): ?Account
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::account() . " WHERE id_conta = %d AND fk_id_usuario = %d",
            $id,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    public function findAllByUser(int $userId): array
    {
        global $wpdb;

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . TableNames::account() . " WHERE fk_id_usuario = %d ORDER BY criada_em",
            $userId
        ), ARRAY_A);

        return array_map([$this, 'mapRow'], $rows ?: []);
    }

    public function findPrincipal(int $userId): ?Account
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::account() . " WHERE fk_id_usuario = %d AND papel = 'PRINCIPAL'",
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    public function hasPrincipal(int $userId): bool
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . TableNames::account() . " WHERE fk_id_usuario = %d AND papel = 'PRINCIPAL'",
            $userId
        ));

        return (int) $count > 0;
    }

    public function countByUser(int $userId): int
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . TableNames::account() . " WHERE fk_id_usuario = %d",
            $userId
        ));

        return (int) $count;
    }

    private function mapRow(array $row): Account
    {
        return new Account(
            (int) $row['id_conta'],
            (int) $row['fk_id_usuario'],
            $row['nome'],
            AccountRole::from($row['papel']),
            new \DateTimeImmutable($row['criada_em'])
        );
    }
}
