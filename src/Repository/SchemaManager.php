<?php

declare(strict_types=1);

namespace WorldQuest\Repository;

use wpdb;

final class SchemaManager
{
    public function __construct(private readonly wpdb $wpdb)
    {
    }

    public function createOrUpdateTables(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $this->wpdb->get_charset_collate();
        $quests = $this->wpdb->prefix . 'wq_quests';
        $nodes = $this->wpdb->prefix . 'wq_nodes';
        $choices = $this->wpdb->prefix . 'wq_choices';

        dbDelta("CREATE TABLE {$quests} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset};");

        dbDelta("CREATE TABLE {$nodes} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            quest_id BIGINT UNSIGNED NOT NULL,
            node_code VARCHAR(32) NOT NULL,
            content LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_quest_node_code (quest_id, node_code),
            KEY idx_quest_id (quest_id)
        ) {$charset};");

        dbDelta("CREATE TABLE {$choices} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            quest_id BIGINT UNSIGNED NOT NULL,
            node_id BIGINT UNSIGNED NOT NULL,
            next_node_id BIGINT UNSIGNED NOT NULL,
            label VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_node_id (node_id),
            KEY idx_next_node_id (next_node_id)
        ) {$charset};");
    }
}
