<?php

declare(strict_types=1);

namespace WorldQuest\Service;

use DomainException;

final class QuestFlowService
{
    private const MAX_CHOICES = 6;

    public function __construct(private readonly object $choiceRepository)
    {
    }

    public function buildAvailableActions(int $nodeId): array
    {
        $choices = $this->choiceRepository->findByNodeId($nodeId);
        $activeChoices = array_values(array_filter($choices, static fn (array $choice): bool => ($choice['status'] ?? '') === 'active'));

        if (count($activeChoices) > self::MAX_CHOICES) {
            throw new DomainException(sprintf('Node %d has %d active choices, limit is %d.', $nodeId, count($activeChoices), self::MAX_CHOICES));
        }

        return array_map(
            static fn (array $choice): array => [
                'choice_id' => (int) $choice['id'],
                'label' => (string) $choice['label'],
                'next_node_id' => (int) $choice['next_node_id'],
            ],
            $activeChoices
        );
    }

    public function validateBranchMerges(array $choices): bool
    {
        $incoming = [];
        foreach ($choices as $choice) {
            $nextNodeId = (int) $choice['next_node_id'];
            $incoming[$nextNodeId] = ($incoming[$nextNodeId] ?? 0) + 1;
        }

        foreach ($incoming as $count) {
            if ($count > 1) {
                return true;
            }
        }

        return false;
    }
}
