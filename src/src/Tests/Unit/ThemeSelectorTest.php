<?php

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ThemeSelectorTest extends TestCase
{
    public function testOnlyTheSettingsThemeSelectorHasTheBoundedWidth(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-app .sgfp-setting select {', $source);
        self::assertStringContainsString('width: clamp(120px, 100%, 150px);', $source);
        self::assertStringContainsString('min-width: 120px;', $source);
        self::assertStringNotContainsString('.sgfp-app select {\n  width:', $source);
    }

    public function testThemeSelectorKeepsItsOptionsAndAccessibleFocusStyle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-app .sgfp-setting select:focus', $source);
        self::assertStringContainsString('.sgfp-app select{-webkit-appearance:none', $source);
    }

    public function testThemeSelectorIsGroupedBeforeTheOtherSettings(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('sgfp-settings-group sgfp-theme-group', $source);
        self::assertStringContainsString('aria-labelledby="sgfp-theme-title"', $source);
        self::assertStringContainsString('sgfp-theme-title', $source);
        self::assertStringContainsString('sgfp-settings-group .sgfp-setting', file_get_contents(dirname(__DIR__, 3) . '/assets/app.css'));
    }
}
