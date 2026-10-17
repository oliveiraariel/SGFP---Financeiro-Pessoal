<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class AnnualChartAxisTest extends TestCase
{
    private string $asset;

    protected function setUp(): void
    {
        $asset = file_get_contents(__DIR__ . '/../../../assets/app.js');
        self::assertIsString($asset);
        $this->asset = $asset;
    }

    public function testAnnualChartKeepsMonetaryYAxisLabelsWithoutVerticalTitle(): void
    {
        self::assertStringContainsString('money(value)', $this->asset);
        self::assertStringNotContainsString('sgfp-chart-axis-title', $this->asset);
        self::assertStringContainsString('Array.from({ length: 5 }', $this->asset);
    }

    public function testAnnualChartRemovesTheTickRulerAndLabel(): void
    {
        self::assertStringNotContainsString('sgfp-chart-tick-control', $this->asset);
        self::assertStringNotContainsString('Marcações do eixo', $this->asset);
        self::assertStringContainsString('chartTickIndices(normalizedPoints)', $this->asset);
    }

    public function testAnnualChartKeepsEmptyAndConstantScaleGuards(): void
    {
        self::assertStringContainsString('if (!finite.length) return { min: -1, max: 1 };', $this->asset);
        self::assertStringContainsString('Math.max(Math.abs(center) * 0.12, 1)', $this->asset);
    }

    public function testAnnualChartUsesAReadableViewportHeight(): void
    {
        self::assertStringContainsString('height = 360', $this->asset);
    }
}
