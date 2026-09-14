<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Backup\RestorationImportPlan;
use SGFP\Application\Ports\RestorationPersistence;
use SGFP\Infrastructure\Database\TableNames;

final class WpRestorationPersistence implements RestorationPersistence
{
    private const TABLES = [
        'accounts' => ['table' => 'account', 'id' => 'id_conta'],
        'categories' => ['table' => 'category', 'id' => 'id_categoria'],
        'recurrences' => ['table' => 'recurrence', 'id' => 'id_recorrencia'],
        'commitments' => ['table' => 'commitment', 'id' => 'id_compromisso'],
        'entries' => ['table' => 'entry', 'id' => 'id_lancamento'],
    ];

    public function replace(RestorationImportPlan $plan): void
    {
        global $wpdb;

        $this->assertPlan($plan);
        $this->assertTransactionalEngines();

        foreach (array_reverse(array_keys(self::TABLES)) as $section) {
            $table = TableNames::{self::TABLES[$section]['table']}();

            if ($wpdb->query($wpdb->prepare(
                "DELETE FROM {$table} WHERE fk_id_usuario = %d",
                $plan->userId
            )) === false) {
                throw new \RuntimeException('Falha ao apagar dados da restauração: ' . $wpdb->last_error);
            }
        }

        $physical = [];

        foreach (array_slice($plan->replacementOrder, 0, -1) as $section) {
            foreach ($plan->records[$section] ?? [] as $record) {
                $data = $this->row($section, $record, $plan->userId, $physical);
                $table = TableNames::{self::TABLES[$section]['table']}();

                if ($wpdb->insert($table, $data['values'], $data['formats']) === false) {
                    throw new \RuntimeException('Falha ao importar ' . $section . ': ' . $wpdb->last_error);
                }

                $physical[(string) $record['logical_key']] = (int) $wpdb->insert_id;
            }
        }

        if (!in_array($plan->theme, ['light', 'dark'], true)) {
            throw new \RuntimeException('Falha ao aplicar o tema da restauração: valor inválido.');
        }

        $metaTable = $wpdb->usermeta;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT umeta_id FROM {$metaTable} WHERE user_id = %d AND meta_key = %s LIMIT 1 FOR UPDATE",
            $plan->userId,
            'sgfp_theme'
        ));

        $this->assertQuerySucceeded(
            $existing !== null || $wpdb->last_error === '',
            'Falha ao bloquear o tema da restauração'
        );

        if ($existing !== null) {
            $ok = $wpdb->update(
                $metaTable,
                ['meta_value' => $plan->theme],
                ['umeta_id' => (int) $existing, 'user_id' => $plan->userId, 'meta_key' => 'sgfp_theme'],
                ['%s'],
                ['%d', '%d', '%s']
            );
        } else {
            $ok = $wpdb->insert(
                $metaTable,
                ['user_id' => $plan->userId, 'meta_key' => 'sgfp_theme', 'meta_value' => $plan->theme],
                ['%d', '%s', '%s']
            );
        }

        if ($ok === false) {
            throw new \RuntimeException('Falha ao aplicar o tema da restauração: ' . $wpdb->last_error);
        }
    }

    public function verify(RestorationImportPlan $plan): array
    {
        global $wpdb;

        $counts = [];

        foreach (array_keys(self::TABLES) as $section) {
            $table = TableNames::{self::TABLES[$section]['table']}();
            $value = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE fk_id_usuario = %d",
                $plan->userId
            ));

            $this->assertQuerySucceeded($value !== null, 'Falha ao verificar ' . $section);
            $counts[$section] = (int) $value;
        }

        $refsValue = $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM ' . TableNames::entry()
            . ' e LEFT JOIN ' . TableNames::account()
            . ' a ON a.id_conta=e.fk_id_conta AND a.fk_id_usuario=e.fk_id_usuario'
            . ' LEFT JOIN ' . TableNames::commitment()
            . ' c ON c.id_compromisso=e.fk_id_compromisso AND c.fk_id_usuario=e.fk_id_usuario'
            . ' WHERE e.fk_id_usuario=%d'
            . ' AND (a.id_conta IS NULL OR (e.fk_id_compromisso IS NOT NULL AND c.id_compromisso IS NULL))',
            $plan->userId
        ));

        $this->assertQuerySucceeded($refsValue !== null, 'Falha ao verificar referências');

        $theme = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s LIMIT 1",
            $plan->userId,
            'sgfp_theme'
        ));

        $this->assertQuerySucceeded($theme !== null || $wpdb->last_error === '', 'Falha ao verificar tema');

        $refs = (int) $refsValue;

        return [
            'counts' => $counts,
            'references' => $refs,
            'owner' => $plan->userId,
            'theme' => (string) $theme,
            'invariants' => $refs === 0,
        ];
    }

    private function assertPlan(RestorationImportPlan $plan): void
    {
        if ($plan->userId <= 0
            || $plan->replacementOrder !== ['accounts', 'categories', 'recurrences', 'commitments', 'entries', 'theme']) {
            throw new \InvalidArgumentException('Plano de restauração inválido.');
        }
    }

    private function assertTransactionalEngines(): void
    {
        global $wpdb;

        $tables = [
            $wpdb->usermeta,
            TableNames::restorationToken(),
            ...array_map(
                static fn (array $table): string => TableNames::{$table['table']}(),
                self::TABLES
            ),
        ];

        $placeholders = implode(',', array_fill(0, count($tables), '%s'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES"
            . " WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$placeholders})",
            ...$tables
        ), ARRAY_A);

        if ($rows === null || $wpdb->last_error !== '') {
            throw new \RuntimeException('Preflight de engine falhou: ' . $wpdb->last_error);
        }

        $engines = [];
        foreach ($rows as $row) {
            $engines[(string) $row['TABLE_NAME']] = strtoupper((string) $row['ENGINE']);
        }

        foreach ($tables as $table) {
            if (($engines[$table] ?? null) !== 'INNODB') {
                throw new \RuntimeException("Preflight de engine falhou: {$table} requer InnoDB.");
            }
        }
    }

    private function assertQuerySucceeded(bool $condition, string $message): void
    {
        global $wpdb;

        if (!$condition) {
            throw new \RuntimeException($message . ': ' . $wpdb->last_error);
        }
    }

    private function row(string $section, array $record, int $userId, array $physical): array
    {
        $id = static function (string $key) use ($record, $physical): ?int {
            if (($record[$key] ?? null) === null) {
                return null;
            }

            return $physical[(string) $record[$key]]
                ?? throw new \InvalidArgumentException('Referência física ausente.');
        };

        return match ($section) {
            // Compatibilidade transitória até a migração física V8.
            'accounts' => [
                'values' => [
                    'fk_id_usuario' => $userId,
                    'nome' => $record['name'],
                    'papel' => 'PRINCIPAL',
                ],
                'formats' => ['%d', '%s', '%s'],
            ],
            'categories' => [
                'values' => [
                    'fk_id_usuario' => $userId,
                    'nome' => $record['name'],
                ],
                'formats' => ['%d', '%s'],
            ],
            'recurrences' => [
                'values' => [
                    'fk_id_usuario' => $userId,
                    'inicio_mes' => $record['startsIn'],
                    'quantidade_meses' => $record['monthsCount'],
                    'encerrada_no_mes' => $record['endedIn'],
                ],
                'formats' => ['%d', '%s', '%d', '%s'],
            ],
            // Compatibilidade transitória: a coluna tipo ainda existe na V7.
            'commitments' => [
                'values' => [
                    'fk_id_usuario' => $userId,
                    'fk_id_categoria' => $id('categoryId'),
                    'fk_id_recorrencia' => $id('recurrenceId'),
                    'nome' => $record['name'],
                    'valor' => $record['amount'],
                    'tipo' => 'PADRAO',
                    'natureza' => $record['nature'],
                    'mes_referencia' => $record['referenceMonth'],
                    'status' => $record['status'],
                ],
                'formats' => ['%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s'],
            ],
            'entries' => [
                'values' => [
                    'fk_id_usuario' => $userId,
                    'fk_id_compromisso' => $id('commitmentId'),
                    'fk_id_conta' => $id('accountId'),
                    'origem' => $record['origin'],
                    'nome' => $record['name'],
                    'valor' => $record['amount'],
                    'tipo_efeito' => $record['effectType'],
                    'data_efetivacao' => $record['settledAt'],
                    'descricao' => $record['description'] ?? null,
                    'estado' => $record['state'],
                    'desfeito_em' => $record['undoneAt'] ?? null,
                ],
                'formats' => ['%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s'],
            ],
            default => throw new \InvalidArgumentException('Seção de restauração inválida.'),
        };
    }
}
