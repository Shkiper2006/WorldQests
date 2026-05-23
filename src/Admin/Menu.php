<?php

declare(strict_types=1);

namespace WorldQuest\Admin;

use wpdb;

final class Menu
{
    private string $questsTable;
    private string $nodesTable;

    public function __construct(private readonly string $pluginFile, private readonly wpdb $wpdb)
    {
        $this->questsTable = $this->wpdb->prefix . 'world_quests';
        $this->nodesTable = $this->wpdb->prefix . 'world_quest_nodes';
    }

    public function register(): void
    {
        add_menu_page('Мировой квест', 'Мировой квест', 'manage_options', 'worldquest', [$this, 'renderQuestsPage'], 'dashicons-location-alt', 56);
        add_submenu_page('worldquest', 'Квесты', 'Квесты', 'manage_options', 'worldquest', [$this, 'renderQuestsPage']);
        add_submenu_page('worldquest', 'Узлы', 'Узлы', 'manage_options', 'world-quest-nodes', [$this, 'renderNodesPage']);
        add_submenu_page('worldquest', 'Модерация', 'Модерация', 'manage_options', 'world-quest-moderation', [$this, 'renderModerationPage']);
        add_submenu_page('worldquest', 'Настройки', 'Настройки', 'manage_options', 'world-quest-settings', [$this, 'renderSettingsPage']);
    }

    public function registerSettings(): void
    {
        register_setting('world_quest_appearance', 'world_quest_appearance', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeAppearanceSettings'],
            'default' => [],
        ]);
        register_setting('world_quest_security', 'world_quest_security', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeSecuritySettings'],
            'default' => [],
        ]);

        add_settings_section('world_quest_appearance_section', 'Внешний вид', static fn() => null, 'world-quest-settings');

        $fields = [
            'primary_color' => 'Основной цвет',
            'secondary_color' => 'Вторичный цвет',
            'font_family' => 'Шрифт',
            'background' => 'Фон',
            'button_style' => 'Стиль кнопок',
            'block_size' => 'Размер блоков',
        ];

        foreach ($fields as $key => $label) {
            add_settings_field($key, $label, [$this, 'renderAppearanceField'], 'world-quest-settings', 'world_quest_appearance_section', ['key' => $key]);
        }

        add_settings_section('world_quest_security_section', 'Безопасность', static fn() => null, 'world-quest-settings');
        add_settings_field('recaptcha_site_key', 'reCAPTCHA site key', [$this, 'renderSecurityField'], 'world-quest-settings', 'world_quest_security_section', ['key' => 'recaptcha_site_key']);
        add_settings_field('recaptcha_secret', 'reCAPTCHA secret', [$this, 'renderSecurityField'], 'world-quest-settings', 'world_quest_security_section', ['key' => 'recaptcha_secret']);
    }

    public function sanitizeSecuritySettings(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        return [
            'recaptcha_site_key' => sanitize_text_field((string) ($input['recaptcha_site_key'] ?? '')),
            'recaptcha_secret' => sanitize_text_field((string) ($input['recaptcha_secret'] ?? '')),
        ];
    }

    public function sanitizeAppearanceSettings(mixed $input): array
    {
        $input = is_array($input) ? $input : [];

        return [
            'primary_color' => sanitize_hex_color((string) ($input['primary_color'] ?? '')) ?: '#000000',
            'secondary_color' => sanitize_hex_color((string) ($input['secondary_color'] ?? '')) ?: '#ffffff',
            'font_family' => sanitize_text_field((string) ($input['font_family'] ?? '')),
            'background' => sanitize_text_field((string) ($input['background'] ?? '')),
            'button_style' => sanitize_text_field((string) ($input['button_style'] ?? '')),
            'block_size' => sanitize_text_field((string) ($input['block_size'] ?? '')),
        ];
    }

    public function renderAppearanceField(array $args): void
    {
        $key = (string) ($args['key'] ?? '');
        $options = get_option('world_quest_appearance', []);
        $value = esc_attr((string) ($options[$key] ?? ''));
        echo "<input type='text' class='regular-text' name='world_quest_appearance[{$key}]' value='{$value}' />";
    }

    public function renderSecurityField(array $args): void
    {
        $key = (string) ($args['key'] ?? '');
        $options = get_option('world_quest_security', []);
        $value = esc_attr((string) ($options[$key] ?? ''));
        echo "<input type='text' class='regular-text' name='world_quest_security[{$key}]' value='{$value}' />";
    }

    public function renderQuestsPage(): void
    {
        $this->handleQuestActions();
        $quests = $this->wpdb->get_results("SELECT * FROM {$this->questsTable} ORDER BY id DESC", ARRAY_A);
        echo '<div class="wrap"><h1>Квесты</h1>';
        $this->renderQuestForm();
        echo '<table class="widefat"><thead><tr><th>ID</th><th>Название</th><th>Статус</th><th>Действия</th></tr></thead><tbody>';
        foreach ($quests as $quest) {
            $id = (int) $quest['id'];
            echo '<tr><td>' . $id . '</td><td>' . esc_html($quest['title']) . '</td><td>' . esc_html($quest['status']) . '</td><td>'
                . '<a href="' . esc_url(add_query_arg(['page' => 'worldquest', 'edit' => $id], admin_url('admin.php'))) . '">Ред.</a> | '
                . '<a href="' . esc_url(wp_nonce_url(add_query_arg(['page' => 'worldquest', 'delete' => $id], admin_url('admin.php')), 'wq_delete_quest_' . $id)) . '">Удалить</a></td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function renderNodesPage(): void
    {
        $this->handleNodeActions();
        $nodes = $this->wpdb->get_results("SELECT * FROM {$this->nodesTable} ORDER BY id DESC", ARRAY_A);
        echo '<div class="wrap"><h1>Узлы</h1>';
        $this->renderNodeForm();
        echo '<table class="widefat"><thead><tr><th>ID</th><th>Quest ID</th><th>Код</th><th>Статус</th><th>Действия</th></tr></thead><tbody>';
        foreach ($nodes as $node) {
            $id = (int) $node['id'];
            echo '<tr><td>' . $id . '</td><td>' . (int) $node['quest_id'] . '</td><td>' . esc_html($node['node_code']) . '</td><td>' . esc_html($node['status']) . '</td><td>'
                . '<a href="' . esc_url(add_query_arg(['page' => 'world-quest-nodes', 'edit' => $id], admin_url('admin.php'))) . '">Ред.</a> | '
                . '<a href="' . esc_url(wp_nonce_url(add_query_arg(['page' => 'world-quest-nodes', 'delete' => $id], admin_url('admin.php')), 'wq_delete_node_' . $id)) . '">Удалить</a></td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function renderModerationPage(): void
    {
        $this->handleModerationActions();
        $quests = $this->wpdb->get_results($this->wpdb->prepare("SELECT id,title,status FROM {$this->questsTable} WHERE status = %s", 'pending_moderation'), ARRAY_A);
        $nodes = $this->wpdb->get_results($this->wpdb->prepare("SELECT id,node_code,status FROM {$this->nodesTable} WHERE status = %s", 'pending_moderation'), ARRAY_A);
        echo '<div class="wrap"><h1>Модерация</h1><h2>Квесты</h2><ul>';
        foreach ($quests as $quest) {
            $id = (int) $quest['id'];
            echo '<li>#' . $id . ' ' . esc_html($quest['title']) . ' '
                . '<a href="' . esc_url(wp_nonce_url(add_query_arg(['page' => 'world-quest-moderation', 'approve_quest' => $id], admin_url('admin.php')), 'wq_moderate_quest_' . $id)) . '">Одобрить</a> '
                . '<a href="' . esc_url(wp_nonce_url(add_query_arg(['page' => 'world-quest-moderation', 'reject_quest' => $id], admin_url('admin.php')), 'wq_moderate_quest_' . $id)) . '">Отклонить</a></li>';
        }
        echo '</ul><h2>Узлы</h2><ul>';
        foreach ($nodes as $node) {
            $id = (int) $node['id'];
            echo '<li>#' . $id . ' ' . esc_html($node['node_code']) . ' '
                . '<a href="' . esc_url(wp_nonce_url(add_query_arg(['page' => 'world-quest-moderation', 'approve_node' => $id], admin_url('admin.php')), 'wq_moderate_node_' . $id)) . '">Одобрить</a> '
                . '<a href="' . esc_url(wp_nonce_url(add_query_arg(['page' => 'world-quest-moderation', 'reject_node' => $id], admin_url('admin.php')), 'wq_moderate_node_' . $id)) . '">Отклонить</a></li>';
        }
        echo '</ul></div>';
    }

    public function renderSettingsPage(): void
    {
        echo '<div class="wrap"><h1>Настройки плагина</h1><form method="post" action="options.php">';
        settings_fields('world_quest_appearance');
        settings_fields('world_quest_security');
        do_settings_sections('world-quest-settings');
        submit_button('Сохранить');
        echo '</form></div>';
    }

    private function renderQuestForm(): void { echo '<form method="post">'; wp_nonce_field('wq_save_quest'); echo '<input type="hidden" name="wq_action" value="save_quest"><input type="text" name="title" placeholder="Название" required><select name="status"><option>draft</option><option>published</option><option>pending_moderation</option></select>'; submit_button('Сохранить квест'); echo '</form>'; }
    private function renderNodeForm(): void { echo '<form method="post">'; wp_nonce_field('wq_save_node'); echo '<input type="hidden" name="wq_action" value="save_node"><input type="number" name="quest_id" placeholder="Quest ID" required><input type="text" name="node_code" placeholder="Код" required><textarea name="content" required></textarea><select name="status"><option>draft</option><option>published</option><option>pending_moderation</option></select><input type="number" name="sort_order" value="0">'; submit_button('Сохранить узел'); echo '</form>'; }

    private function handleQuestActions(): void
    {
        if (($_POST['wq_action'] ?? '') === 'save_quest' && check_admin_referer('wq_save_quest')) {
            $this->wpdb->insert($this->questsTable, ['title' => sanitize_text_field((string) $_POST['title']), 'status' => sanitize_text_field((string) $_POST['status'])]);
        }
        if (isset($_GET['delete']) && wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'wq_delete_quest_' . (int) $_GET['delete'])) {
            $this->wpdb->delete($this->questsTable, ['id' => (int) $_GET['delete']]);
        }
    }

    private function handleNodeActions(): void
    {
        if (($_POST['wq_action'] ?? '') === 'save_node' && check_admin_referer('wq_save_node')) {
            $this->wpdb->insert($this->nodesTable, ['quest_id' => (int) $_POST['quest_id'], 'node_code' => sanitize_text_field((string) $_POST['node_code']), 'content' => wp_kses_post((string) $_POST['content']), 'status' => sanitize_text_field((string) $_POST['status']), 'sort_order' => (int) $_POST['sort_order']]);
        }
        if (isset($_GET['delete']) && wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'wq_delete_node_' . (int) $_GET['delete'])) {
            $this->wpdb->delete($this->nodesTable, ['id' => (int) $_GET['delete']]);
        }
    }

    private function handleModerationActions(): void
    {
        if (isset($_GET['approve_quest']) && wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'wq_moderate_quest_' . (int) $_GET['approve_quest'])) {
            $this->wpdb->update($this->questsTable, ['status' => 'published'], ['id' => (int) $_GET['approve_quest']]);
        }
        if (isset($_GET['reject_quest']) && wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'wq_moderate_quest_' . (int) $_GET['reject_quest'])) {
            $this->wpdb->update($this->questsTable, ['status' => 'draft'], ['id' => (int) $_GET['reject_quest']]);
        }
        if (isset($_GET['approve_node']) && wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'wq_moderate_node_' . (int) $_GET['approve_node'])) {
            $this->wpdb->update($this->nodesTable, ['status' => 'published'], ['id' => (int) $_GET['approve_node']]);
        }
        if (isset($_GET['reject_node']) && wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'wq_moderate_node_' . (int) $_GET['reject_node'])) {
            $this->wpdb->update($this->nodesTable, ['status' => 'draft'], ['id' => (int) $_GET['reject_node']]);
        }
    }
}
