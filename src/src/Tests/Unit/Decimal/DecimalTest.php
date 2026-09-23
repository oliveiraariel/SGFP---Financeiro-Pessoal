<?php
declare(strict_types=1);
namespace SGFP\Tests\Unit\Decimal;
use PHPUnit\Framework\TestCase;
use SGFP\Domain\Models\Decimal;

final class DecimalTest extends TestCase
{
    /** @dataProvider values */
    public function testBrazilianValuesBecomeCanonical(string $input, string $expected): void
    { self::assertSame($expected, Decimal::normalize($input)); }

    public static function values(): iterable
    { yield ['0,00', '0.00']; yield ['2,00', '2.00']; yield ['123,55', '123.55']; yield ['1.234,56', '1234.56']; }

    public function testMalformedValuesAreRejected(): void
    { $this->expectException(\InvalidArgumentException::class); Decimal::normalize('1,2'); }
}
