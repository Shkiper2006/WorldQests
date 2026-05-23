<?php

declare(strict_types=1);

namespace WorldQuest\Frontend;

use RuntimeException;
use wpdb;

final class Shortcodes
{
    private string $questsTable;
    private string $nodesTable;
    private string $choicesTable;

    public function __construct(private readonly string $pluginFile, ?wpdb $database = null)
    {
        global $wpdb;
        $db = $database ?? $wpdb;
        if (!($db instanceof wpdb)) {
            throw new RuntimeException('wpdb unavailable');
        }

        $this->wpdb = $db;
        $this->questsTable = $db->prefix . 'world_quests';
        $this->nodesTable = $db->prefix . 'world_quest_nodes';
        $this->choicesTable = $db->prefix . 'world_quest_choices';
    }

    private wpdb $wpdb;

    public function register(): void
    {
        add_shortcode('world_quests', [$this, 'renderQuestsList']);
        add_shortcode('world_quest', [$this, 'renderQuestViewer']);
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
    }

    public function registerAssets(): void
    {
        wp_register_script(
            'world-quest-viewer',
            plugins_url('assets/js/viewer.js', $this->pluginFile),
            [],
            filemtime(plugin_dir_path($this->pluginFile) . 'assets/js/viewer.js') ?: false,
            true
        );
    }

    public function renderQuestsList(array $atts = []): string
    {
        unset($atts);

        $quests = $this->wpdb->get_results("SELECT id, title, slug, status FROM {$this->questsTable} WHERE status='published' ORDER BY id DESC", ARRAY_A) ?: [];

        ob_start();
        $template = plugin_dir_path($this->pluginFile) . 'templates/quests-list.php';
        include $template;
        return (string) ob_get_clean();
    }

    public function renderQuestViewer(array $atts = []): string
    {
        $atts = shortcode_atts(['id' => 0], $atts, 'world_quest');
        $questId = (int) $atts['id'];
        if ($questId <= 0) {
            return '<div class="world-quest-error">' . esc_html__('Quest ID is required.', 'world-quest') . '</div>';
        }

        $quest = $this->wpdb->get_row($this->wpdb->prepare("SELECT id, title, slug, status FROM {$this->questsTable} WHERE id=%d", $questId), ARRAY_A);
        if (!is_array($quest)) {
            return '<div class="world-quest-error">' . esc_html__('Quest not found.', 'world-quest') . '</div>';
        }

        $nodes = $this->wpdb->get_results($this->wpdb->prepare("SELECT id, node_code, content, sort_order FROM {$this->nodesTable} WHERE quest_id=%d AND status='published' ORDER BY sort_order ASC, id ASC", $questId), ARRAY_A) ?: [];
        $choices = $this->wpdb->get_results($this->wpdb->prepare("SELECT id, parent_node_id, target_node_code, label, sort_order FROM {$this->choicesTable} WHERE quest_id=%d AND status='published' ORDER BY sort_order ASC, id ASC", $questId), ARRAY_A) ?: [];

        if ($nodes === []) {
            return '<div class="world-quest-error">' . esc_html__('No published nodes yet.', 'world-quest') . '</div>';
        }

        wp_enqueue_script('world-quest-viewer');
        wp_add_inline_script('world-quest-viewer', 'window.WorldQuestViewerData = ' . wp_json_encode([
            'questId' => $questId,
            'nodes' => $nodes,
            'choices' => $choices,
            'ctaLabel' => __('Добавить свой вариант развития событий', 'world-quest'),
            'restUrl' => esc_url_raw(rest_url('worldquest/v1')),
            'recaptchaSiteKey' => (string) (get_option('world_quest_security', [])['recaptcha_site_key'] ?? ''),
        ]) . ';', 'before');

        ob_start();
        $template = plugin_dir_path($this->pluginFile) . 'templates/quest-viewer.php';
        include $template;
        return (string) ob_get_clean();
    }
}
