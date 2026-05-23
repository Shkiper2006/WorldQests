<?php

declare(strict_types=1);

namespace WorldQuest\Migrations;

use wpdb;

final class Installer
{
    public const DB_VERSION_OPTION = 'worldquest_db_version';
    public const DB_VERSION = '1.0.0';

    public static function activate(): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb)) {
            return;
        }

        $installer = new self($wpdb);
        $installer->maybeUpgrade();
    }

    public function __construct(private readonly wpdb $wpdb)
    {
    }

    public function maybeUpgrade(): void
    {
        $installedVersion = (string) get_option(self::DB_VERSION_OPTION, '0.0.0');

        if (version_compare($installedVersion, self::DB_VERSION, '>=')) {
            return;
        }

        $this->runDbDelta();
        $this->seedDemoData();

        update_option(self::DB_VERSION_OPTION, self::DB_VERSION, false);
    }

    private function runDbDelta(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $this->wpdb->get_charset_collate();
        $quests = $this->wpdb->prefix . 'world_quests';
        $nodes = $this->wpdb->prefix . 'world_quest_nodes';
        $choices = $this->wpdb->prefix . 'world_quest_choices';

        dbDelta("CREATE TABLE {$quests} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(190) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_slug (slug),
            KEY idx_status (status)
        ) {$charset};");

        dbDelta("CREATE TABLE {$nodes} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            quest_id BIGINT UNSIGNED NOT NULL,
            node_code VARCHAR(32) NOT NULL,
            parent_node_id BIGINT UNSIGNED NULL,
            target_node_code VARCHAR(32) NULL,
            content LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_quest_node_code (quest_id, node_code),
            KEY idx_quest_id (quest_id),
            KEY idx_parent_node_id (parent_node_id),
            KEY idx_target_node_code (target_node_code),
            KEY idx_status (status)
        ) {$charset};");

        dbDelta("CREATE TABLE {$choices} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            quest_id BIGINT UNSIGNED NOT NULL,
            parent_node_id BIGINT UNSIGNED NOT NULL,
            target_node_code VARCHAR(32) NOT NULL,
            label VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_quest_id (quest_id),
            KEY idx_parent_node_id (parent_node_id),
            KEY idx_target_node_code (target_node_code),
            KEY idx_status (status)
        ) {$charset};");
    }

    private function seedDemoData(): void
    {
        $questsTable = $this->wpdb->prefix . 'world_quests';
        $nodesTable = $this->wpdb->prefix . 'world_quest_nodes';
        $choicesTable = $this->wpdb->prefix . 'world_quest_choices';

        $exists = (int) $this->wpdb->get_var("SELECT COUNT(1) FROM {$questsTable}");
        if ($exists > 0) {
            return;
        }

        $this->wpdb->insert($questsTable, [
            'title' => 'Demo Quest: Lost Compass',
            'slug' => 'demo-lost-compass',
            'status' => 'published',
        ]);

        $questId = (int) $this->wpdb->insert_id;
        if ($questId <= 0) {
            return;
        }

        $nodes = [
            ['START', null, null, 'Вы входите в туманный порт и слышите зов о помощи.', 1],
            ['MARKET', null, null, 'На рынке можно купить карту или поспрашивать моряков.', 2],
            ['DOCKS', null, null, 'У доков штормит. Один капитан ищет штурмана.', 3],
            ['CAVE', null, null, 'Пещера хранит старый компас и странные знаки.', 4],
            ['REEF', null, null, 'Рифы опасны, но за ними виден свет маяка.', 5],
            ['FINALE', null, null, 'Вы возвращаете компас владельцу и завершаете квест.', 6],
        ];

        $nodeIdByCode = [];

        foreach ($nodes as [$nodeCode, $parentNodeId, $targetNodeCode, $content, $sortOrder]) {
            $this->wpdb->insert($nodesTable, [
                'quest_id' => $questId,
                'node_code' => $nodeCode,
                'parent_node_id' => $parentNodeId,
                'target_node_code' => $targetNodeCode,
                'content' => $content,
                'status' => 'published',
                'sort_order' => $sortOrder,
            ]);

            $nodeIdByCode[$nodeCode] = (int) $this->wpdb->insert_id;
        }

        $choices = [
            ['START', 'MARKET', 'Пойти на рынок', 1],
            ['START', 'DOCKS', 'Идти к докам', 2],
            ['MARKET', 'CAVE', 'Купить карту и идти к пещере', 1],
            ['MARKET', 'DOCKS', 'Поговорить с капитаном', 2],
            ['DOCKS', 'REEF', 'Выйти в море через рифы', 1],
            ['DOCKS', 'CAVE', 'Сойти на берег у пещеры', 2],
            ['CAVE', 'FINALE', 'Взять компас и вернуться', 1],
            ['REEF', 'FINALE', 'Следовать к маяку', 1],
        ];

        foreach ($choices as [$fromCode, $toCode, $label, $sortOrder]) {
            $this->wpdb->insert($choicesTable, [
                'quest_id' => $questId,
                'parent_node_id' => $nodeIdByCode[$fromCode],
                'target_node_code' => $toCode,
                'label' => $label,
                'status' => 'published',
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
