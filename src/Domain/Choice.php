<?php

declare(strict_types=1);

namespace WorldQuest\Domain;

final class Choice
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $questId,
        public readonly int $nodeId,
        public readonly int $nextNodeId,
        public readonly string $label,
        public readonly string $status = 'draft',
        public readonly int $sortOrder = 0,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }
}
