<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Command\Db;

use Montikids\MagentoCliUtil\Command\AbstractCommand;
use Montikids\MagentoCliUtil\Model\Command\Step\StepCustomSqlQueries;
use Montikids\MagentoCliUtil\Model\Command\Step\StepExecuteN98Commands;
use Montikids\MagentoCliUtil\Model\Command\Step\StepAnonymizeTablesData;
use Montikids\MagentoCliUtil\Model\Config\AnonymizeReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Anonymize Magento tables fields data according to the configuration
 */
class AnonymizeCommand extends AbstractCommand
{
    public const NAME = 'db:anonymize';
    public const DESCRIPTION = 'Anonymize sensitive data in the Magento database';

    /**
     * @var AnonymizeReader
     */
    private $configReader;

    /**
     * @var StepAnonymizeTablesData
     */
    private $stepAnonymize;

    /**
     * @var StepCustomSqlQueries
     */
    private $stepCustomQueries;

    /**
     * @var StepExecuteN98Commands
     */
    private $stepN98;

    /**
     * Initialize command
     */
    public function __construct()
    {
        parent::__construct();

        $this->configReader = new AnonymizeReader();
        $this->stepAnonymize = new StepAnonymizeTablesData();
        $this->stepCustomQueries = new StepCustomSqlQueries();
        $this->stepN98 = new StepExecuteN98Commands();
    }

    /**
     * @inheritDoc
     */
    protected function getHelpInformation(): string
    {
        $result = <<<TEXT
Replaces customer sensitive data using a pattern specified in the corresponding config file.
Using the corresponding config sections you also can run any custom SQL queries and specific N98 Magerun util
commands afterward.
Check the README.md file, config examples, or the repository docs for more details.
TEXT;

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->wrapExecute(function () use ($output) {
            $envType = $this->getConfiguredEnvironmentType();
            $envConfig = $this->configReader->readMergedConfig($envType);

            $this->stepAnonymize->execute($envConfig, $output);
            $this->stepCustomQueries->execute($envConfig, $output);
            $this->stepN98->execute($envConfig, $output);
        }, $output);

        return $result;
    }
}
