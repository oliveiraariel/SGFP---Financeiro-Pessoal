<?php

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class FormFieldStylesTest extends TestCase
{
    public function testEditableFieldsHaveScopedReadableStatesAcrossThemes(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-app .sgfp-field textarea', $source);
        self::assertStringContainsString('.sgfp-app .sgfp-field [role="combobox"]', $source);
        self::assertStringContainsString('background-color:#fff !important', $source);
        self::assertStringContainsString('color:#183033 !important', $source);
        self::assertStringContainsString('input::placeholder', $source);
        self::assertStringContainsString('textarea::placeholder', $source);
        self::assertStringContainsString('input:focus', $source);
        self::assertStringContainsString('textarea:focus', $source);
        self::assertStringContainsString('input:disabled', $source);
        self::assertStringContainsString('textarea:disabled', $source);
        self::assertStringContainsString('[aria-disabled="true"]', $source);
        self::assertStringContainsString('color-scheme:light', $source);
    }

    public function testFieldContractIsScopedToTheSgfpApplication(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringNotContainsString('body input {', $source);
        self::assertStringNotContainsString('input { background-color:#fff', $source);
        self::assertStringContainsString('.sgfp-app .sgfp-field input', $source);
    }
}
