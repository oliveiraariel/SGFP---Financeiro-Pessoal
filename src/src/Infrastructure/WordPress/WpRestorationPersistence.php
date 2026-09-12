<?php
declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Backup\RestorationImportPlan;
use SGFP\Application\Ports\RestorationPersistence;
use SGFP\Infrastructure\Database\TableNames;

/** Performs the physical, user-scoped replacement for a validated plan. */
final class WpRestorationPersistence implements RestorationPersistence
{
    private const TABLES = [
        'accounts' => ['table' => 'account', 'id' => 'id_conta'],
        'categories' => ['table' => 'category', 'id' => 'id_categoria'],
        'recurrences' => ['table' => 'recurrence', 'id' => 'id_recorrencia'],
        'commitments' => ['table' => 'commitment', 'id' => 'id_compromisso'],
        'transfers' => ['table' => 'transfer', 'id' => 'fk_id_compromisso'],
        'entries' => ['table' => 'entry', 'id' => 'id_lancamento'],
    ];

    public function replace(RestorationImportPlan $plan): void
    {
        global $wpdb;
        $this->assertPlan($plan);
        $this->assertTransactionalEngines();

        foreach (array_reverse(array_keys(self::TABLES)) as $section) {
            $table = TableNames::{self::TABLES[$section]['table']}();
            if ($wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE fk_id_usuario = %d", $plan->userId)) === false) {
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
                $key = (string) $record['logical_key'];
                $physical[$key] = (int) ($section === 'transfers' ? $data['values']['fk_id_compromisso'] : $wpdb->insert_id);
            }
        }
        if (!in_array($plan->theme, ['light', 'dark'], true)) {
            throw new \RuntimeException('Falha ao aplicar o tema da restauração: valor inválido.');
        }
        $metaTable = $wpdb->usermeta;
        $metaKey = 'sgfp_theme';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT umeta_id FROM {$metaTable} WHERE user_id = %d AND meta_key = %s LIMIT 1 FOR UPDATE",
            $plan->userId, $metaKey
        ));
        $this->assertQuerySucceeded($existing !== null || $wpdb->last_error === '', 'Falha ao bloquear o tema da restauração');
        if ($existing !== null) {
            $ok = $wpdb->update($metaTable, ['meta_value' => $plan->theme], ['umeta_id' => (int) $existing, 'user_id' => $plan->userId, 'meta_key' => $metaKey], ['%s'], ['%d', '%d', '%s']);
        } else {
            $ok = $wpdb->insert($metaTable, ['user_id' => $plan->userId, 'meta_key' => $metaKey, 'meta_value' => $plan->theme], ['%d', '%s', '%s']);
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
            $value = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE fk_id_usuario = %d", $plan->userId));
            $this->assertQuerySucceeded($value !== null, 'Falha ao verificar ' . $section);
            $counts[$section] = (int) $value;
        }
        $refsValue = $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM ' . TableNames::entry() . ' e LEFT JOIN ' . TableNames::account() . ' a ON a.id_conta=e.fk_id_conta AND a.fk_id_usuario=e.fk_id_usuario LEFT JOIN ' . TableNames::commitment() . ' c ON c.id_compromisso=e.fk_id_compromisso AND c.fk_id_usuario=e.fk_id_usuario WHERE e.fk_id_usuario=%d AND (a.id_conta IS NULL OR (e.fk_id_compromisso IS NOT NULL AND c.id_compromisso IS NULL))',
            $plan->userId
        ));
        $this->assertQuerySucceeded($refsValue !== null, 'Falha ao verificar referências');
        $theme = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s LIMIT 1", $plan->userId, 'sgfp_theme'));
        $this->assertQuerySucceeded($theme !== null || $wpdb->last_error === '', 'Falha ao verificar tema');
        $refs = (int) $refsValue;
        return ['counts' => $counts, 'references' => $refs, 'owner' => $plan->userId, 'theme' => (string) $theme, 'invariants' => $refs === 0];
    }

    private function assertPlan(RestorationImportPlan $plan): void
    {
        if ($plan->userId <= 0 || $plan->replacementOrder !== ['accounts','categories','recurrences','commitments','transfers','entries','theme']) {
            throw new \InvalidArgumentException('Plano de restauração inválido.');
        }
    }

    private function assertTransactionalEngines(): void
    {
        global $wpdb;
        $tables = [$wpdb->usermeta, TableNames::restorationToken(), ...array_map(static fn(array $t): string => TableNames::{ $t['table'] }(), self::TABLES)];
        $placeholders = implode(',', array_fill(0, count($tables), '%s'));
        $rows = $wpdb->get_results($wpdb->prepare("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$placeholders})", ...$tables), ARRAY_A);
        if ($rows === null || $wpdb->last_error !== '') throw new \RuntimeException('Preflight de engine falhou: ' . $wpdb->last_error);
        $engines = [];
        foreach ($rows as $row) $engines[(string) $row['TABLE_NAME']] = strtoupper((string) $row['ENGINE']);
        foreach ($tables as $table) if (($engines[$table] ?? null) !== 'INNODB') throw new \RuntimeException("Preflight de engine falhou: {$table} requer InnoDB.");
    }

    private function assertQuerySucceeded(bool $condition, string $message): void
    {
        global $wpdb;
        if (!$condition) throw new \RuntimeException($message . ': ' . $wpdb->last_error);
    }

    private function row(string $section, array $r, int $userId, array $physical): array
    {
        $id = static fn(string $key): ?int => $r[$key] === null ? null : ($physical[(string) $r[$key]] ?? throw new \InvalidArgumentException('Referência física ausente.'));
        $base = ['fk_id_usuario' => $userId];
        $formats = ['%d'];
        if ($section === 'accounts') { $base += ['nome'=>$r['name'], 'papel'=>$r['role']]; $formats += ['%s','%s']; }
        elseif ($section === 'categories') { $base += ['nome'=>$r['name']]; $formats += ['%s']; }
        elseif ($section === 'recurrences') { $base += ['inicio_mes'=>$r['startsIn'], 'quantidade_meses'=>$r['monthsCount'], 'encerrada_no_mes'=>$r['endedIn']]; $formats += ['%s','%d','%s']; }
        elseif ($section === 'commitments') { $base += ['fk_id_categoria'=>$id('categoryId'), 'fk_id_recorrencia'=>$id('recurrenceId'), 'nome'=>$r['name'], 'valor'=>$r['amount'], 'tipo'=>$r['type'], 'natureza'=>$r['nature'], 'mes_referencia'=>$r['referenceMonth'], 'status'=>$r['status']]; $formats += ['%d','%d','%s','%f','%s','%s','%s','%s']; }
        elseif ($section === 'transfers') { $base = ['fk_id_compromisso'=>$id('commitmentId'), 'fk_id_usuario'=>$userId, 'fk_id_conta_origem'=>$id('sourceAccountId'), 'fk_id_conta_destino'=>$id('targetAccountId')]; $formats=['%d','%d','%d','%d']; }
        elseif ($section === 'entries') { $base += ['fk_id_compromisso'=>$id('commitmentId'), 'fk_id_conta'=>$id('accountId'), 'origem'=>$r['origin'], 'nome'=>$r['name'], 'valor'=>$r['amount'], 'tipo_efeito'=>$r['effectType'], 'data_efetivacao'=>$r['settledAt'], 'descricao'=>$r['description'] ?? null, 'estado'=>$r['state'], 'desfeito_em'=>$r['undoneAt'] ?? null]; $formats += ['%d','%d','%s','%s','%f','%s','%s','%s','%s','%s']; }
        return ['values'=>$base, 'formats'=>$formats];
    }
}
