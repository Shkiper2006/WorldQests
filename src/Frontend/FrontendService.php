<?php

declare(strict_types=1);

namespace WorldQuest\Frontend;

final class FrontendService
{
    private Shortcodes $shortcodes;
    private Blocks $blocks;

    public function __construct(string $pluginFile)
    {
        $this->shortcodes = new Shortcodes($pluginFile);
        $this->blocks = new Blocks($pluginFile, $this->shortcodes);
    }

    public function register(): void
    {
        $this->shortcodes->register();
        $this->blocks->register();
    }
}
