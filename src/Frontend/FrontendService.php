<?php

declare(strict_types=1);

namespace WorldQuest\Frontend;

final class FrontendService
{
    private Shortcodes $shortcodes;

    public function __construct(string $pluginFile)
    {
        $this->shortcodes = new Shortcodes($pluginFile);
    }

    public function register(): void
    {
        $this->shortcodes->register();
    }
}
