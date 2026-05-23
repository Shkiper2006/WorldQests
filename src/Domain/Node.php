<?php

declare(strict_types=1);

namespace WorldQuest\Domain;

final class Node
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $questId,
        public readonly string $nodeCode,
        public readonly string $content,
        public readonly string $status = 'draft',
        public readonly int $sortOrder = 0,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }
}
