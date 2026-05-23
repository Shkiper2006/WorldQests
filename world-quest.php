<?php
/**
 * Plugin Name: World Quest
 * Plugin URI:  https://example.com/world-quest
 * Description: Foundation plugin for World Quest features: domain models, REST API, admin UI, and frontend integrations.
 * Version:     0.1.1
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author:      World Quest Team
 * Text Domain: worldquest
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'WorldQuest\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative);

        $paths = [
            __DIR__ . '/src/' . $relativePath . '.php',
            __DIR__ . '/migrations/' . $relativePath . '.php',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });
}

if (! class_exists('WorldQuest\\Plugin') || ! class_exists('WorldQuest\\Migrations\\Installer')) {
    add_action('admin_notices', static function (): void {
        if (! current_user_can('activate_plugins')) {
            return;
        }

        echo '<div class="notice notice-error"><p>'
            . esc_html__('World Quest failed to load classes. Please check plugin files or run Composer autoload generation.', 'worldquest')
            . '</p></div>';
    });
    return;
}

register_activation_hook(__FILE__, ['WorldQuest\\Migrations\\Installer', 'activate']);
add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('worldquest', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

$plugin = new WorldQuest\Plugin(__FILE__);
$plugin->boot();
