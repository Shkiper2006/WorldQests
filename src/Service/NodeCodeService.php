<?php

declare(strict_types=1);

namespace WorldQuest\Service;

use InvalidArgumentException;

final class NodeCodeService
{
    public function generate(int $sequence): string
    {
        if ($sequence < 1) {
            throw new InvalidArgumentException('Node sequence must be greater than 0.');
        }

        return sprintf('N%03d', $sequence);
    }

    public function validate(string $nodeCode): bool
    {
        return preg_match('/^N\d{3}$/', $nodeCode) === 1;
    }
}
