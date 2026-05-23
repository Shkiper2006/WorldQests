<?php

declare(strict_types=1);

use WorldQuest\Service\NodeCodeService;
use WorldQuest\Service\QuestFlowService;

require_once __DIR__ . '/../vendor/autoload.php';

$nodeCodeService = new NodeCodeService();
assert($nodeCodeService->generate(1) === 'N001');
assert($nodeCodeService->validate('N999') === true);
assert($nodeCodeService->validate('X01') === false);

$choiceRepositoryStub = new class {
    public function findByNodeId(int $nodeId): array
    {
        if ($nodeId === 100) {
            return [
                ['id' => 1, 'label' => 'A', 'next_node_id' => 2, 'status' => 'active'],
                ['id' => 2, 'label' => 'B', 'next_node_id' => 2, 'status' => 'active'],
            ];
        }

        return [];
    }
};

$flowService = new QuestFlowService($choiceRepositoryStub);
$actions = $flowService->buildAvailableActions(100);
assert(count($actions) === 2);
assert($flowService->validateBranchMerges([
    ['next_node_id' => 2],
    ['next_node_id' => 2],
    ['next_node_id' => 3],
]) === true);

print "Quest business rules checks passed\n";
