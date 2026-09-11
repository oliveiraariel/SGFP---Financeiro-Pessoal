<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\TransactionManager;

final class WpTransactionManager implements TransactionManager
{
    public function begin(): void
    {
        // WordPress não expõe begin explícito de forma padronizada;
        // usamos query direta para transações MySQL/MariaDB.
        $this->query('START TRANSACTION');
    }

    public function commit(): void
    {
        $this->query('COMMIT');
    }

    public function rollback(): void
    {
        $this->query('ROLLBACK');
    }

    public function transactional(callable $action): mixed
    {
        $this->begin();

        try {
            $result = $action();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    private function query(string $sql): void
    {
        global $wpdb;
        $wpdb->query($sql);
    }
}
