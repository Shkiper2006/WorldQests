<?php
/**
 * Plugin Name: World Quest
 * Plugin URI:  https://example.com/world-quest
 * Description: Foundation plugin for World Quest features: domain models, REST API, admin UI, and frontend integrations.
 * Version:     0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author:      World Quest Team
 * Text Domain: world-quest
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

register_activation_hook(__FILE__, ['WorldQuest\\Migrations\\Installer', 'activate']);
add_action('plugins_loaded', ['WorldQuest\\Migrations\\Installer', 'activate']);

$plugin = new WorldQuest\Plugin(__FILE__);
$plugin->boot();
