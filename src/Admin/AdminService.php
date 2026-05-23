<?php

declare(strict_types=1);

namespace WorldQuest\Admin;

use wpdb;

final class AdminService
{
    private ?Menu $menu = null;

    public function __construct(private readonly string $pluginFile)
    {
        global $wpdb;
        if ($wpdb instanceof wpdb) {
            $this->menu = new Menu($pluginFile, $wpdb);
        }
    }

    public function registerMenu(): void
    {
        $this->menu?->register();
    }

    public function registerSettings(): void
    {
        $this->menu?->registerSettings();
    }

    public function enqueueBlockEditorAssets(): void
    {
        $stylePath = plugin_dir_path($this->pluginFile) . 'assets/css/editor.css';
        $scriptPath = plugin_dir_path($this->pluginFile) . 'assets/js/editor.js';

        wp_enqueue_style('world-quest-editor', plugin_dir_url($this->pluginFile) . 'assets/css/editor.css', [], file_exists($stylePath) ? (string) filemtime($stylePath) : null);
        wp_enqueue_script('world-quest-editor', plugin_dir_url($this->pluginFile) . 'assets/js/editor.js', ['wp-blocks', 'wp-element', 'wp-i18n'], file_exists($scriptPath) ? (string) filemtime($scriptPath) : null, true);
    }
}
