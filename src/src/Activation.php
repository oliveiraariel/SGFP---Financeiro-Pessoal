<?php

declare(strict_types=1);

namespace SGFP;

use SGFP\Infrastructure\Database\SchemaMigrator;

final class Activation
{
    public static function run(): void
    {
        SchemaMigrator::migrate();

        update_option('sgfp_version', SGFP_VERSION, false);
    }
}
