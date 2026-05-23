<?php

declare(strict_types=1);

namespace WorldQuest\Admin;

final class AdminService
{
    public function __construct(private readonly string $pluginFile)
    {
    }

    public function registerMenu(): void
    {
        add_menu_page(
            __('World Quest', 'world-quest'),
            __('World Quest', 'world-quest'),
            'manage_options',
            'world-quest',
            [$this, 'renderDashboard'],
            'dashicons-location-alt',
            56
        );
    }

    public function enqueueBlockEditorAssets(): void
    {
        $stylePath = plugin_dir_path($this->pluginFile) . 'assets/css/editor.css';
        $scriptPath = plugin_dir_path($this->pluginFile) . 'assets/js/editor.js';

        wp_enqueue_style(
            'world-quest-editor',
            plugin_dir_url($this->pluginFile) . 'assets/css/editor.css',
            [],
            file_exists($stylePath) ? (string) filemtime($stylePath) : null
        );

        wp_enqueue_script(
            'world-quest-editor',
            plugin_dir_url($this->pluginFile) . 'assets/js/editor.js',
            ['wp-blocks', 'wp-element', 'wp-i18n'],
            file_exists($scriptPath) ? (string) filemtime($scriptPath) : null,
            true
        );
    }

    public function renderDashboard(): void
    {
        echo '<div class="wrap"><h1>' . esc_html__('World Quest', 'world-quest') . '</h1></div>';
    }
}
