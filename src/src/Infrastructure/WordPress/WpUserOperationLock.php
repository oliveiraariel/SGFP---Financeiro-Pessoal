<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\UserOperationLock;

final class WpUserOperationLock implements UserOperationLock
{
    public function acquire(int $userId): void
    {
        global $wpdb;
        $name = 'sgfp-user-' . $userId;
        $result = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)', $name));
        if ((int) $result !== 1) throw new \RuntimeException('Não foi possível obter o lock da operação.');
    }

    public function release(int $userId): void
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare('SELECT RELEASE_LOCK(%s)', 'sgfp-user-' . $userId));
    }
}
