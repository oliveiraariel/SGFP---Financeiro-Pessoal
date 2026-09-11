<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Services\ThemeService;

final class ThemeServiceTest extends TestCase
{
    public function testReturnsDefaultThemeWhenUnset(): void
    {
        $preferences = $this->createMock(UserPreferenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $preferences->method('get')->with('theme', 1)->willReturn(null);

        $service = new ThemeService($preferences, $userContext);

        $this->assertSame('light', $service->get());
    }

    public function testReturnsStoredTheme(): void
    {
        $preferences = $this->createMock(UserPreferenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $preferences->method('get')->with('theme', 1)->willReturn('dark');

        $service = new ThemeService($preferences, $userContext);

        $this->assertSame('dark', $service->get());
    }

    public function testUpdatesTheme(): void
    {
        $preferences = $this->createMock(UserPreferenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $preferences->expects($this->once())->method('set')->with('theme', 1, 'dark');

        $service = new ThemeService($preferences, $userContext);

        $this->assertSame('dark', $service->update('dark'));
    }

    public function testRejectsInvalidTheme(): void
    {
        $preferences = $this->createMock(UserPreferenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $service = new ThemeService($preferences, $userContext);

        $this->expectException(\InvalidArgumentException::class);
        $service->update('blue');
    }
}
