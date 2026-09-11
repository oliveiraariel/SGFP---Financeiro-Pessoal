<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\Database;

use RuntimeException;

final class SchemaMigrator
{
    private const OPTION_NAME = 'sgfp_schema_version';

    /**
     * @var array<string, string>
     */
    private const MIGRATIONS = [
        '1.0.0' => 'migrateTo100',
    ];

    public static function migrate(): void
    {
        $current = get_option(self::OPTION_NAME, '0.0.0');

        foreach (self::MIGRATIONS as $version => $method) {
            if (version_compare($current, $version, '>=')) {
                continue;
            }

            self::{$method}($version);
            update_option(self::OPTION_NAME, $version, false);
            $current = $version;
        }
    }

    private static function migrateTo100(string $version): void
    {
        global $wpdb;

        require_once \ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $sql = Schema::getCreateTablesSql($charset);

        $previousHideErrors = $wpdb->hide_errors();

        try {
            foreach (self::splitStatements($sql) as $statement) {
                $result = $wpdb->query($statement);

                if ($result === false) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration %s failed: %s',
                            $version,
                            $wpdb->last_error
                        )
                    );
                }
            }
        } finally {
            if ($previousHideErrors === true) {
                $wpdb->show_errors();
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function splitStatements(string $sql): array
    {
        $statements = [];

        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            $statements[] = $statement;
        }

        return $statements;
    }
}
