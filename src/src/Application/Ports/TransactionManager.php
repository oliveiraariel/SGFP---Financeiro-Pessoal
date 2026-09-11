<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

interface TransactionManager
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;

    /**
     * @template T
     * @param callable(): T $action
     * @return T
     */
    public function transactional(callable $action): mixed;
}
