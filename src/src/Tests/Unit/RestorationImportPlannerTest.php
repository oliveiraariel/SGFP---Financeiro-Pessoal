<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\RestorationImportPlanner;
use SGFP\Application\Backup\StagedBackupDecoder;

final class RestorationImportPlannerTest extends TestCase
{
    public function testPlansV2OrderAndRemapsNullableReferences(): void
    {
        $payload = [
            'metadata' => [
                'schema' => 'sgfp-backup',
                'version' => 2,
                'owner' => 7,
                'origin' => 'manual',
                'created_at' => '2026-09-14T00:00:00+00:00',
            ],
            'theme' => 'dark',
            'accounts' => [[
                'id' => 10,
                'userId' => 7,
                'name' => 'Minha Conta',
                'createdAt' => '2026-09-14T00:00:00+00:00',
            ]],
            'categories' => [[
                'id' => 20,
                'userId' => 7,
                'name' => 'C',
                'createdAt' => '2026-09-14T00:00:00+00:00',
            ]],
            'recurrences' => [],
            'commitments' => [[
                'id' => 30,
                'userId' => 7,
                'categoryId' => 20,
                'recurrenceId' => null,
                'name' => 'X',
                'amount' => 10.0,
                'nature' => 'SAIDA',
                'referenceMonth' => '2026-09-01',
                'status' => 'PENDENTE',
                'createdAt' => '2026-09-14T00:00:00+00:00',
            ]],
            'entries' => [[
                'id' => 40,
                'userId' => 7,
                'accountId' => 10,
                'commitmentId' => null,
                'origin' => 'SALDO_INICIAL',
                'name' => 'Saldo inicial',
                'amount' => 100.0,
                'effectType' => 'ENTRADA',
                'settledAt' => '2026-09-01T00:00:00+00:00',
                'description' => null,
                'state' => 'ATIVO',
                'createdAt' => '2026-09-01T00:00:00+00:00',
                'undoneAt' => null,
            ]],
        ];

        $plan = (new RestorationImportPlanner())->plan(
            (new StagedBackupDecoder())->decode($payload, 7),
            7
        );

        self::assertSame(
            ['accounts', 'categories', 'recurrences', 'commitments', 'entries', 'theme'],
            $plan->replacementOrder
        );
        self::assertSame('categories:20', $plan->records['commitments'][0]['categoryId']);
        self::assertNull($plan->records['commitments'][0]['recurrenceId']);
        self::assertSame('accounts:10', $plan->records['entries'][0]['accountId']);
    }
}
