<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Service;

use Montikids\MagentoCliUtil\Enum\FileDirInterface;

/**
 * N98 Magerun 2 command executor
 */
class RunN98Command
{
    private const BINARY_PATH = FileDirInterface::DIR_VENDOR_BIN . '/n98-magerun2';

    /**
     * Runs the specified N98 command and return its output
     *
     * @param string $command
     * @param array<string> $arguments
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    public function execute(string $command, array $arguments = []): ?string
    {
        $argumentsStr = implode(' ', $arguments);
        $commandParts = [self::BINARY_PATH, $command, $argumentsStr, '--skip-root-check'];
        $strToExecute = trim(implode(' ', $commandParts));
        $result = (shell_exec($strToExecute) ?? null);

        return $result;
    }
}
