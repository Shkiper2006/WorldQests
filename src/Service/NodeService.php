<?php

declare(strict_types=1);

namespace WorldQuest\Service;

use DomainException;
use WorldQuest\Domain\Node;
use WorldQuest\Repository\NodeRepository;

final class NodeService
{
    public function __construct(private readonly NodeRepository $nodeRepository)
    {
    }

    public function assertNodeCodeUniqueInQuest(Node $node, ?int $excludeNodeId = null): void
    {
        if ($this->nodeRepository->existsNodeCodeInQuest($node->questId, $node->nodeCode, $excludeNodeId)) {
            throw new DomainException(sprintf('Node code "%s" already exists in quest %d.', $node->nodeCode, $node->questId));
        }
    }
}
