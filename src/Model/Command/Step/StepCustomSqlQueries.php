<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\Config\AbstractYamlInterface;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Db\Connection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains method to process the @see AbstractYamlInterface::SECTION_SQL_QUERY config section
 */
class StepCustomSqlQueries
{
    use OutputFormatTrait;

    /**
     * @param array<string, mixed> $config
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    public function execute(array $config, OutputInterface $output): void
    {
        $customQueries = $config[AbstractYamlInterface::SECTION_SQL_QUERY] ?? [];

        if (false === empty($customQueries)) {
            $this->printTitle('Executing custom SQL queries...', $output);

            $connection = Connection::getInstance();

            foreach ($customQueries as $query) {
                $this->printPrimary("- {$query}", $output);

                $connection->query($query);
                $affectedRows = $connection->affectedRows();

                $this->printSecondary("Affected {$affectedRows} rows", $output);
                $this->printSeparatorSmall($output);
            }
        } else {
            if (true === $output->isVerbose()) {
                $this->printSecondary("No custom SQL queries to execute", $output);
            }
        }
    }
}
