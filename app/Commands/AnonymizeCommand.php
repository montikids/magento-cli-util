<?php
declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace App\Commands;

use App\Helper\Anonymize;
use App\Helper\Db;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Anonymization of the client's personal data
 *
 * Class AnonymizeCommand
 */
class AnonymizeCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('db:anonymize')
            ->setDescription('Anonymize personal customer data')
            ->setHelp('Replacing customer personal data with template values');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $output->writeln('<info>Read mysql params connection from app/etc/env.php</info>');

        $cryptKey = trim($this->readEnvFile('crypt.key', $output));
        $db = $this->getDbInstance($output);

        $anonymize = new Anonymize($cryptKey);
        $queries = $anonymize->getPreparedSqlQueries();

        $output->writeln('<info>Anonymize tables:</info>');
        foreach ($queries as $alias => $sql) {
            $output->writeln("<comment>Anonymize table: {$alias} </comment>");
            $db->query($sql);
        }

        $customQueries = $anonymize->getSqlQueriesFromConfig();

        if (count($customQueries)) {
            $output->writeln('<info>Run custom queries:</info>');
            foreach ($customQueries as $sql) {
                $output->writeln("<comment>Run '{$sql}'</comment>");
                $db->query($sql);
            }
        }
        $db->close();

        $mageRunComands = $anonymize->getMagerun2Commands();
        if(count($mageRunComands)){
            $output->writeln("<info>Run magerun2 commands:</info>");
            foreach ($mageRunComands as $command){
                if($command) {
                    $output->writeln("<info>Run '{$command}'</info>");
                    $output->writeln("<comment>{$this->execMageRun($command)}</comment>");
                }
            }
        }

        return 1;
    }
}
