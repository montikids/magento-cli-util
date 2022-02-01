<?php
declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Add variable to app/etc/env.php
 *
 * Class ConfigureEnvVariable
 */
class ConfigureEnvVariable extends AbstractCommand
{
    public const ARGUMENT_NAME_ENV = 'env';

    protected function configure(): void
    {
        $allowedEnv = implode(',', $this->allowedEnv);

        $this->setName('configure:init')
            ->setDescription('Add current environment variable to app/etc/env.php')
            ->setHelp(
                'Required to apply different environment configuration profiles. ' .
                'And prohibition of execution on the production environment'
            )
            ->addArgument(self::ARGUMENT_NAME_ENV, InputArgument::REQUIRED, 'Environment value: ' . $allowedEnv);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $envName = $input->getArgument(self::ARGUMENT_NAME_ENV);
        $cliEnvPath = self::CLI_UTIL_ENV_PATH;

        $output->writeln("<info>Adding value '{$envName}' to app/etc/env.php (cli_util.environment)</info>");
        $result = $this->execMageRun("config:env:set {$cliEnvPath} {$envName}");
        $output->writeln("<comment>{$result}</comment>");
        return 1;
    }
}
