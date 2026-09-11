<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;

final class ThemeService
{
    private const KEY = 'theme';
    private const DEFAULT_THEME = 'light';
    private const ALLOWED_THEMES = ['light', 'dark'];

    public function __construct(
        private readonly UserPreferenceRepository $preferences,
        private readonly UserContext $userContext,
    ) {
    }

    public function get(): string
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $theme = $this->preferences->get(self::KEY, $userId);

        return in_array($theme, self::ALLOWED_THEMES, true) ? $theme : self::DEFAULT_THEME;
    }

    public function update(string $theme): string
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        if (!in_array($theme, self::ALLOWED_THEMES, true)) {
            throw new \InvalidArgumentException('O tema deve ser light ou dark.');
        }

        $this->preferences->set(self::KEY, $userId, $theme);

        return $theme;
    }
}
