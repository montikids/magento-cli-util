<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Service;

use Montikids\MagentoCliUtil\Enum\N98CommandInterface;

/**
 * N98 Magerun 2 command executor
 */
class RunN98Command
{
    /**
     * @var string
     */
    private const BINARY_PATH = 'vendor/bin/n98-magerun2';

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
        $commandExists = (true === in_array($command, N98CommandInterface::ALL_COMMANDS));

        if (false === $commandExists) {
            throw new \InvalidArgumentException("Command '{$command}' doesn't exist or isn't allowed to be run");
        }

        $argumentsStr = implode(' ', $arguments);
        $commandParts = [self::BINARY_PATH, $command, $argumentsStr];
        $strToExecute = trim(implode(' ', $commandParts));
        $result = (shell_exec($strToExecute) ?? null);

        return $result;
    }
}
