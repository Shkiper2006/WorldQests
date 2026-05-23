<?php

declare(strict_types=1);

namespace WorldQuest\Repository;

use WorldQuest\Domain\Quest;
use wpdb;

final class QuestRepository
{
    public function __construct(private readonly wpdb $wpdb, private readonly string $tableName)
    {
    }

    public function create(Quest $quest): int
    {
        $this->wpdb->query($this->wpdb->prepare(
            "INSERT INTO {$this->tableName} (title, status) VALUES (%s, %s)",
            $quest->title,
            $quest->status
        ));

        return (int) $this->wpdb->insert_id;
    }

    public function findById(int $id): ?array
    {
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->tableName} WHERE id = %d", $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function update(int $id, Quest $quest): bool
    {
        $sql = $this->wpdb->prepare(
            "UPDATE {$this->tableName} SET title = %s, status = %s WHERE id = %d",
            $quest->title,
            $quest->status,
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
