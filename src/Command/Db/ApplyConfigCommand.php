<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Command\Db;

use Montikids\MagentoCliUtil\Command\AbstractCommand;
use Montikids\MagentoCliUtil\Model\Command\Step\StepApplyConfig;
use Montikids\MagentoCliUtil\Model\Command\Step\StepCustomSqlQueries;
use Montikids\MagentoCliUtil\Model\Command\Step\StepExecuteN98Commands;
use Montikids\MagentoCliUtil\Model\Config\StoreConfigReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Applied config values to the 'core_config_data' Magento DB table
 */
class ApplyConfigCommand extends AbstractCommand
{
    public const NAME = 'db:apply-config';
    public const DESCRIPTION = 'Update "core_config_data" Magento DB table with the config file values';

    /**
     * @var StoreConfigReader
     */
    private $configReader;

    /**
     * @var StepApplyConfig
     */
    private $stepApplyConfig;

    /**
     * @var StepCustomSqlQueries
     */
    private $stepCustomQueries;

    /**
     * @var StepExecuteN98Commands
     */
    private $stepN98;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        parent::__construct();

        $this->configReader = new StoreConfigReader();
        $this->stepApplyConfig = new StepApplyConfig();
        $this->stepCustomQueries = new StepCustomSqlQueries();
        $this->stepN98 = new StepExecuteN98Commands();
    }

    /**
     * @inheritDoc
     */
    protected function getHelpInformation(): string
    {
        $result = <<<TEXT
Replaces the 'core_config_data' table values according to the corresponding config file.
Using the corresponding config sections you also can run any custom SQL queries and specific N98 Magerun util
commands afterward.
Check the README.md file, config examples, or the repository docs for more details.
TEXT;

        return $result;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \SodiumException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->wrapExecute(function () use ($output) {
            $envType = $this->getConfiguredEnvironmentType();
            $envConfig = $this->configReader->readMergedConfig($envType);

            $this->stepApplyConfig->execute($envConfig, $output);
            $this->stepCustomQueries->execute($envConfig, $output);
            $this->stepN98->execute($envConfig, $output);
        }, $output);

        return $result;
    }
}
