<?php

declare(strict_types=1);

namespace WorldQuest\Domain;

final class Quest
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly string $status = 'draft',
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }
}
