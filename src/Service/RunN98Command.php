<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Service;

use Montikids\MagentoCliUtil\Enum\FileDirInterface;
use Montikids\MagentoCliUtil\Exception\N98CommandException;

/**
 * N98 Magerun 2 command executor
 */
class RunN98Command
{
    private const BINARY_PATH = FileDirInterface::DIR_VENDOR_BIN . '/n98-magerun2';

    /**
     * Runs the specified N98 command and returns its output
     *
     * @param string $command Command name, may be followed by raw arguments (e.g. taken from a config file)
     * @param array<string> $arguments Additional arguments, each one is shell-escaped
     * @return string Command stdout
     * @throws N98CommandException
     */
    public function execute(string $command, array $arguments = []): string
    {
        $commandParts = array_merge(
            [escapeshellarg(self::BINARY_PATH), $command],
            array_map('escapeshellarg', $arguments),
            ['--skip-root-check']
        );
        $commandLine = implode(' ', $commandParts);

        $stderrFile = tempnam(sys_get_temp_dir(), 'mk-cli-util-');

        if (false === $stderrFile) {
            throw new N98CommandException("Unable to create a temporary file to run '{$command}'");
        }

        $outputLines = [];
        $exitCode = 0;

        try {
            // stderr goes to a separate file so warnings can't get mixed into the command output
            $isStarted = exec($commandLine . ' 2>' . escapeshellarg($stderrFile), $outputLines, $exitCode);
            $stderr = trim((string)file_get_contents($stderrFile));
        } finally {
            unlink($stderrFile);
        }

        $stdout = implode("\n", $outputLines);

        if ((false === $isStarted) || (0 !== $exitCode)) {
            $details = trim("{$stderr}\n{$stdout}");
            $error = "N98 Magerun 2 command '{$command}' failed with exit code {$exitCode}: {$details}";

            throw new N98CommandException($error);
        }

        return $stdout;
    }
}
