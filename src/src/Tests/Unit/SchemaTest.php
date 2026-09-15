<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Infrastructure\Database\Schema;

final class SchemaTest extends TestCase
{
    protected function setUp(): void
    {
        global $wpdb;

        $wpdb = (object) [
            'users' => 'wp_users',
            'prefix' => 'wp_',
        ];
    }

    public function testFreshInstallCreatesEverySgfpTableWithInnoDb(): void
    {
        global $wpdb;

        $sql = Schema::getCreateTablesSql('DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        self::assertSame(5, substr_count($sql, 'ENGINE=InnoDB'));
        self::assertSame(5, substr_count($sql, 'CREATE TABLE IF NOT EXISTS'));
        self::assertStringNotContainsString(') DEFAULT CHARACTER SET', $sql);
        self::assertStringContainsString('wp_sgfp_conta_financeira', $sql);
        self::assertStringContainsString('wp_sgfp_categoria', $sql);
        self::assertStringContainsString('wp_sgfp_recorrencia', $sql);
        self::assertStringContainsString('wp_sgfp_compromisso_financeiro', $sql);
        self::assertStringContainsString('wp_sgfp_lancamento_financeiro', $sql);
    }
}
