<?php

declare(strict_types=1);

namespace SGFP;

use SGFP\Infrastructure\Database\Schema;

final class Activation
{
    public static function run(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        Schema::createTables();

        update_option('sgfp_version', SGFP_VERSION, false);
    }
}
