<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class AccountViewTest extends TestCase
{
    private function source(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');
    }

    public function testAccountViewRendersAdministrativeAndFinancialDatesSeparately(): void
    {
        $source = $this->source();

        self::assertStringContainsString("account.user_name", $source);
        self::assertStringContainsString("account.financial_start_date", $source);
        self::assertStringContainsString("account.created_at", $source);
        self::assertStringContainsString('Saldo inicial', $source);
        self::assertStringContainsString('Início do histórico financeiro', $source);
        self::assertStringContainsString('Conta criada no sistema', $source);
    }

    public function testAccountViewKeepsCurrentBalanceDerivedFromActiveEntries(): void
    {
        self::assertStringContainsString(
            'Saldo atual · derivado dos lançamentos ativos',
            $this->source()
        );
    }
}
