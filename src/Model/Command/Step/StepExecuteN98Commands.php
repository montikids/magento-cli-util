<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\Config\AbstractYamlInterface;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Service\RunN98Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Processes the @see AbstractYamlInterface::SECTION_N98_MAGERUN2_COMMAND config section by running N98 Magerun 2
 * commands
 */
class StepExecuteN98Commands
{
    use OutputFormatTrait;

    /**
     * @var RunN98Command
     */
    private $n98;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        $this->n98 = new RunN98Command();
    }

    /**
     * @param array<string, mixed> $config
     * @param OutputInterface $output
     * @return void
     * @throws \InvalidArgumentException
     */
    public function execute(array $config, OutputInterface $output): void
    {
        $commandsToExecute = $config[AbstractYamlInterface::SECTION_N98_MAGERUN2_COMMAND] ?? [];

        if (false === empty($commandsToExecute)) {
            $this->printTitle('Running N98 Magerun 2 commands...', $output);

            foreach ($commandsToExecute as $command) {
                $skipCommand = (null === $command);

                if (true === $skipCommand) {
                    continue;
                }

                $this->printPrimary("- Running '{$command}'", $output);

                if (true === $output->isVerbose()) {
                    $executionResult = $this->n98->execute($command);
                    $this->printSecondary("{$executionResult}", $output);
                }
            }
        } else {
            if (true === $output->isVerbose()) {
                $this->printSecondary("No N98 Magerun 2 commands to execute", $output);
            }
        }
    }
}
