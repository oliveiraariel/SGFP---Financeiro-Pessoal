<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\REST\DTOs\SetInitialBalanceRequest;

final class SetInitialBalanceRequestTest extends TestCase
{
    public function testAmountUsesCanonicalDecimalNormalization(): void
    {
        $request = new SetInitialBalanceRequest('1.234,56', null, null, null);
        $request->validate();

        self::assertSame('1234.56', $request->toDecimal());
    }

    public function testNegativeAndZeroAmountsRemainAllowedByInitialBalancePolicy(): void
    {
        foreach (['-10,50', '0,00'] as $amount) {
            $request = new SetInitialBalanceRequest($amount, null, null, null);
            $request->validate();
            self::assertSame($amount === '0,00' ? '0.00' : '-10.50', $request->toDecimal());
        }
    }

    public function testAmountWithWrongPrecisionIsRejected(): void
    {
        $request = new SetInitialBalanceRequest('10,5', null, null, null);

        $this->expectException(\InvalidArgumentException::class);
        $request->validate();
    }
}
