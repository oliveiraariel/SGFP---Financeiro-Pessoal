<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\StagedBackupDecoder;

final class StagedBackupDecoderTest extends TestCase
{
    public function testDecodesValidatedV2Backup(): void
    {
        $backup = (new StagedBackupDecoder())->decode($this->payload(), 7);

        $this->assertSame('dark', $backup->theme);
        $this->assertSame([
            'accounts' => 1,
            'categories' => 0,
            'recurrences' => 0,
            'commitments' => 0,
            'entries' => 0,
        ], $backup->counts());
    }

    /** @dataProvider invalidPayloads */
    public function testRejectsMalformedOrIncompatiblePayload(array $payload): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new StagedBackupDecoder())->decode($payload, 7);
    }

    public static function invalidPayloads(): iterable
    {
        yield 'foreign owner' => [array_replace_recursive(self::basePayload(), ['metadata' => ['owner' => 8]])];
        yield 'unsupported version' => [array_replace_recursive(self::basePayload(), ['metadata' => ['version' => 1]])];
        yield 'unsupported origin' => [array_replace_recursive(self::basePayload(), ['metadata' => ['origin' => 'import']])];
        yield 'unsupported theme' => [array_replace(self::basePayload(), ['theme' => 'blue'])];
        yield 'extra section' => [array_replace(self::basePayload(), ['unexpected' => []])];
        yield 'missing account' => [array_replace(self::basePayload(), ['accounts' => []])];
        yield 'dangling entry reference' => [array_replace(self::basePayload(), [
            'entries' => [[
                'id' => 1,
                'userId' => 7,
                'accountId' => 99,
                'commitmentId' => null,
                'origin' => 'SALDO_INICIAL',
                'effectType' => 'ENTRADA',
                'state' => 'ATIVO',
            ]],
        ])];
    }

    private function payload(): array
    {
        return array_replace(self::basePayload(), ['theme' => 'dark']);
    }

    private static function basePayload(): array
    {
        return [
            'metadata' => [
                'schema' => 'sgfp-backup',
                'version' => 2,
                'owner' => 7,
                'origin' => 'manual',
                'created_at' => '2026-09-14T00:00:00+00:00',
            ],
            'theme' => 'light',
            'accounts' => [[
                'id' => 10,
                'userId' => 7,
                'name' => 'Minha Conta',
                'createdAt' => '2026-09-14T00:00:00+00:00',
            ]],
            'categories' => [],
            'recurrences' => [],
            'commitments' => [],
            'entries' => [],
        ];
    }
}
