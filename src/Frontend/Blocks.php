<?php

declare(strict_types=1);

namespace WorldQuest\Frontend;

final class Blocks
{
    public function __construct(
        private readonly string $pluginFile,
        private readonly Shortcodes $shortcodes,
    ) {
    }

    public function register(): void
    {
        wp_register_script(
            'worldquest-blocks',
            plugins_url('assets/js/blocks.js', $this->pluginFile),
            ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-components'],
            filemtime(plugin_dir_path($this->pluginFile) . 'assets/js/blocks.js') ?: false,
            true
        );

        register_block_type(plugin_dir_path($this->pluginFile) . 'blocks/world-quests-list', [
            'render_callback' => [$this, 'renderWorldQuestsList'],
        ]);

        register_block_type(plugin_dir_path($this->pluginFile) . 'blocks/world-quest-viewer', [
            'render_callback' => [$this, 'renderWorldQuestViewer'],
        ]);
    }

    public function renderWorldQuestsList(array $attributes = []): string
    {
        return $this->shortcodes->renderQuestsList($attributes);
    }

    public function renderWorldQuestViewer(array $attributes = []): string
    {
        $atts = ['id' => (int) ($attributes['questId'] ?? 0)];
        return $this->shortcodes->renderQuestViewer($atts);
    }
}
