<?php
declare(strict_types=1);
namespace SGFP\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class InitialisationModalTest extends TestCase
{
    public function testInitialisationIsModalAndCannotBeDismissedByLocalStorage(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');
        self::assertIsString($source);
        self::assertStringContainsString('data-initial-balance-modal', $source);
        self::assertStringContainsString('aria-modal="true"', $source);
        self::assertStringNotContainsString("localStorage.getItem(promptKey) === 'shown'", $source);
    }
}
