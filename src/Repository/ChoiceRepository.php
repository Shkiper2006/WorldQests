<?php

declare(strict_types=1);

namespace WorldQuest\Repository;

use WorldQuest\Domain\Choice;
use wpdb;

final class ChoiceRepository
{
    public function __construct(private readonly wpdb $wpdb, private readonly string $tableName)
    {
    }

    public function create(Choice $choice): int
    {
        $this->wpdb->query($this->wpdb->prepare(
            "INSERT INTO {$this->tableName} (quest_id, node_id, next_node_id, label, status, sort_order) VALUES (%d, %d, %d, %s, %s, %d)",
            $choice->questId,
            $choice->nodeId,
            $choice->nextNodeId,
            $choice->label,
            $choice->status,
            $choice->sortOrder
        ));

        return (int) $this->wpdb->insert_id;
    }

    public function findById(int $id): ?array
    {
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->tableName} WHERE id = %d", $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function findByNodeId(int $nodeId): array
    {
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->tableName} WHERE node_id = %d ORDER BY sort_order ASC, id ASC", $nodeId);

        return $this->wpdb->get_results($sql, ARRAY_A) ?: [];
    }

    public function update(int $id, Choice $choice): bool
    {
        $sql = $this->wpdb->prepare(
            "UPDATE {$this->tableName} SET next_node_id = %d, label = %s, status = %s, sort_order = %d WHERE id = %d",
            $choice->nextNodeId,
            $choice->label,
            $choice->status,
            $choice->sortOrder,
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
