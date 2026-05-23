<?php

declare(strict_types=1);

namespace WorldQuest\Frontend;

final class FrontendService
{
    public function __construct(private readonly string $pluginFile)
    {
    }

    public function register(): void
    {
        add_shortcode('world_quest', [$this, 'renderShortcode']);
    }

    public function renderShortcode(array $atts = []): string
    {
        $defaults = [
            'title' => __('World Quest', 'world-quest'),
        ];

        $atts = shortcode_atts($defaults, $atts, 'world_quest');

        return sprintf(
            '<div class="world-quest-shortcode"><h2>%s</h2></div>',
            esc_html((string) $atts['title'])
        );
    }
}
