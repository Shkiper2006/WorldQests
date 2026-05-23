<?php
/**
 * Plugin Name: World Quest
 * Plugin URI:  https://example.com/world-quest
 * Description: Foundation plugin for World Quest features: domain models, REST API, admin UI, and frontend integrations.
 * Version:     0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author:      World Quest Team
 * Text Domain: worldquest
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'WorldQuest\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }

        if ($class === 'WorldQuest\\Migrations\\Installer') {
            $migrationPath = __DIR__ . '/migrations/Installer.php';
            if (file_exists($migrationPath)) {
                require_once $migrationPath;
            }
        }
    });
}

if (!class_exists(WorldQuest\Plugin::class)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>'
            . esc_html__('World Quest failed to load classes. Please check plugin files or run Composer autoload generation.', 'worldquest')
            . '</p></div>';
    });

    return;
}

register_activation_hook(__FILE__, ['WorldQuest\\Migrations\\Installer', 'activate']);
add_action('plugins_loaded', ['WorldQuest\\Migrations\\Installer', 'activate']);
add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('worldquest', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

$plugin = new WorldQuest\Plugin(__FILE__);
$plugin->boot();
