<?php

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class AnnualChartSelectorTest extends TestCase
{
    public function testAnnualChartUsesIndependentYearAndKeepsGlobalMonth(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("api('/dashboard/evolution/annual?year=' + year)", $source);
        self::assertStringContainsString('data-chart-year-shift', $source);
        self::assertStringContainsString('evolução mensal', mb_strtolower($source));
        self::assertStringNotContainsString('data-chart-months', $source);
        self::assertStringNotContainsString('chartMonths', $source);
        self::assertStringNotContainsString("/dashboard/evolution/daily?month=", $source);
        self::assertStringContainsString("const mo = monthParam();", $source);
        self::assertStringContainsString("const initialMonthValue = String(month?.value || cfg.month || new Date().getFullYear()", $source);
        self::assertStringContainsString("app.dataset.chartYear = initialMonthValue.slice(0, 4)", $source);
        self::assertStringContainsString("app.dataset.chartYear = String(ev.target.value || '').slice(0, 4)", $source);
        self::assertStringContainsString("const chartMonth = String(year) + '-' + selectedMonth.slice(5, 7);", $source);
        self::assertStringContainsString("history.items = list(history).filter((item) => String(item.date || '').slice(0, 7) === chartMonth);", $source);
        self::assertStringContainsString("api('/dashboard?month=' + encodeURIComponent(mo))", $source);
        self::assertStringContainsString('data-chart-retry', $source);
        self::assertStringContainsString('async function retryChart()', $source);
        self::assertStringContainsString('const currentRetry = app.querySelector(\'.sgfp-evolution [data-chart-retry]\');', $source);
        self::assertStringContainsString('if (loadSequence === sequence && view === \'overview\' && chartYear(app.dataset.chartYear) === year)', $source);
        self::assertStringContainsString('} finally {', $source);
        self::assertStringNotContainsString('positionChartAtSelectedMonth', $source);
    }

    public function testAnnualChartUsesRealFiveDayCalendarTicksAndKeepsEveryDailyPoint(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertMatchesRegularExpression('/const chartTickIndices = \(points, frequency = 5, minimumPixelDistance = \d+\) =>/', $source);
        self::assertStringContainsString('minimumPixelDistance', $source);
        self::assertStringContainsString('elapsedDays % frequency === 0', $source);
        self::assertStringContainsString("+ 'T00:00:00Z'", $source);
        self::assertMatchesRegularExpression('/chartTickIndices\(normalizedPoints\)\.map\(\(index\)/', $source);
        self::assertStringContainsString('const dots = normalizedPoints.map', $source);
        self::assertStringNotContainsString('Math.ceil(points.length / 8)', $source);
        self::assertStringContainsString('elapsedDays % frequency === 0', $source);
    }

    public function testAnnualChartYearNavigationIsKeyboardAccessible(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('data-chart-year-shift', $source);
        self::assertStringContainsString('aria-label="Ano anterior"', $source);
        self::assertStringContainsString('aria-label="Próximo ano"', $source);
        self::assertStringContainsString("app.addEventListener('click'", $source);
        self::assertStringNotContainsString('data-chart-tick-frequency', $source);
        self::assertStringNotContainsString('data-chart-tick-value', $source);
        self::assertStringNotContainsString('chartTickStorageKey', $source);
        self::assertStringNotContainsString('type="range"', $source);
    }

    public function testAnnualChartUsesDynamicVerticalScaleForEmptyConstantVariableAndExpandedSeries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('const chartScale = (values) =>', $source);
        self::assertStringContainsString('Number.isFinite(value)', $source);
        self::assertStringContainsString('if (!finite.length) return { min: -1, max: 1 };', $source);
        self::assertStringContainsString('observedSpan > 0', $source);
        self::assertStringContainsString('Math.abs(center) * 0.12', $source);
        self::assertStringContainsString('const normalizedPoints = points.map', $source);
        self::assertStringContainsString('const values = normalizedPoints.flatMap', $source);
        self::assertStringContainsString('dailySeries = normalizedPoints;', $source);
        self::assertStringContainsString('const scale = chartScale(values);', $source);
        self::assertStringContainsString('const min = scale.min, max = scale.max;', $source);
        self::assertStringContainsString('data-chart-expand', $source);
        self::assertStringContainsString('sgfp-chart-expanded', $source);
        self::assertStringContainsString('height = 360', $source);
    }

    public function testThemeSelectorKeepsACompactBoundedWidth(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-setting select{width:clamp(120px,100%,150px);min-width:120px}', $source);
    }

    public function testAnnualChartKeepsExplicitVerticalAspectRatioWithoutStretchingSvg(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-chart{width:100%;max-width:100%;aspect-ratio:2 / 1}', $source);
        self::assertStringContainsString('.sgfp-chart-expanded .sgfp-chart{width:100%;max-width:100%;aspect-ratio:2 / 1}', $source);
        self::assertStringNotContainsString('preserveAspectRatio="none"', $source);
    }
}
