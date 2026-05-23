<?php

declare(strict_types=1);

namespace WorldQuest\Repository;

use WorldQuest\Domain\Node;
use wpdb;

final class NodeRepository
{
    public function __construct(private readonly wpdb $wpdb, private readonly string $tableName)
    {
    }

    public function create(Node $node): int
    {
        $this->wpdb->query($this->wpdb->prepare(
            "INSERT INTO {$this->tableName} (quest_id, node_code, content, status, sort_order) VALUES (%d, %s, %s, %s, %d)",
            $node->questId,
            $node->nodeCode,
            $node->content,
            $node->status,
            $node->sortOrder
        ));

        return (int) $this->wpdb->insert_id;
    }

    public function findById(int $id): ?array
    {
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->tableName} WHERE id = %d", $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function existsNodeCodeInQuest(int $questId, string $nodeCode, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(1) FROM {$this->tableName} WHERE quest_id = %d AND node_code = %s";
        $args = [$questId, $nodeCode];

        if ($excludeId !== null) {
            $sql .= ' AND id <> %d';
            $args[] = $excludeId;
        }

        return (int) $this->wpdb->get_var($this->wpdb->prepare($sql, ...$args)) > 0;
    }

    public function update(int $id, Node $node): bool
    {
        $sql = $this->wpdb->prepare(
            "UPDATE {$this->tableName} SET node_code = %s, content = %s, status = %s, sort_order = %d WHERE id = %d",
            $node->nodeCode,
            $node->content,
            $node->status,
            $node->sortOrder,
            $id
        );

        return $this->wpdb->query($sql) !== false;
    }

    public function delete(int $id): bool
    {
        $sql = $this->wpdb->prepare("DELETE FROM {$this->tableName} WHERE id = %d", $id);

        return $this->wpdb->query($sql) !== false;
    }
}
