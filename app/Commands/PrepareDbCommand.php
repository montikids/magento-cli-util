<?php
declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace App\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * DB configuration for the environment
 *
 * Class PrepareDbCommand
 */
class PrepareDbCommand extends AbstractCommand
{
    public const METHOD_DELETE = 'delete';
    public const METHOD_ENCRYPT = 'encrypt';

    protected function configure(): void
    {
        $this->setName('db:configure')->setDescription('Changing configuration values in DB');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \SodiumException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $envName = $this->getEnvironmentType($output);

        $db = $this->getDbInstance($output);

        $output->writeln('<info>Configure store_config_data:</info>');
        $config = $this->readConfig($envName);

        foreach ($config['config'] as $scope => $values) {
            foreach ($values as $scopeId => $values1) {
                foreach ($values1 as $configPath => $value) {
                    $this->processingValue($output, (int)$scopeId, $scope, $configPath, $value);
                }
            }
        }

        if (isset($config['sql_query']) && is_array($config['sql_query'])) {
            $output->writeln('<info>Run custom queries:</info>');
            foreach ($config['sql_query'] as $sql) {
                $output->writeln("<comment>Run '{$sql}'</comment>");
                $db->query($sql);
            }
        }

        if (isset($config['magerun2_run']) && is_array($config['magerun2_run'])) {
            $output->writeln("<info>Run magerun2 commands:</info>");
            foreach ($config['magerun2_run'] as $command) {
                if ($command) {
                    $output->writeln("<info>Run '{$command}'</info>");
                    $output->writeln("<comment>{$this->execMageRun($command)}</comment>");
                }
            }
        }

        $db->close();

        return 1;
    }

    /**
     * @param OutputInterface $output
     * @param int $scopeId
     * @param string $scope
     * @param string $configPath
     * @param string|int|array|null $value
     * @return void
     * @throws \SodiumException
     */
    protected function processingValue(OutputInterface $output, int $scopeId, string $scope, string $configPath, $value = null): void
    {
        $configPath = rtrim($configPath);

        $db = $this->getDbInstance($output);
        if (!is_array($value)) {
            $this->setConfigValue($output, $scopeId, $scope, $configPath, $value);
            return;
        }

        $valueConfig = reset($value);
        $method = key($value);

        if ($method === self::METHOD_DELETE) {
            $db->query(
                'DELETE FROM core_config_data WHERE (path = ?) AND (scope = ?) AND (scope_id = ?)',
                [$configPath, $scope, $scopeId]
            );
            $output->writeln('<comment>Deleted: ' . $configPath . '</comment>');
        } elseif ($method == self::METHOD_ENCRYPT) {
            $valueConfig = $this->encrypt($valueConfig);
            $this->setConfigValue($output, $scopeId, $scope, $configPath, $valueConfig);
        }
    }

    /**
     * @param OutputInterface $output
     * @param int $scopeId
     * @param string $scope
     * @param string $configPath
     * @param null $valueConfig
     * @return $this
     * @throws \Exception
     */
    protected function setConfigValue(OutputInterface $output, int $scopeId, string $scope, string $configPath, $valueConfig = null)
    {
        $db = $this->getDbInstance($output);

        $currentConfig = $db->query(
            'SELECT config_id FROM core_config_data WHERE (path = ?) AND (scope = ?) AND (scope_id = ?)',
            [$configPath, $scope, $scopeId]
        )->fetchArray();

        if (count($currentConfig) > 0) {
            $db->query(
                'UPDATE core_config_data SET value=? WHERE config_id = ?',
                [$valueConfig, $currentConfig['config_id']]
            );
        } else {
            $db->query(
                'INSERT INTO core_config_data (scope, scope_id, path, value) VALUES (?, ?, ?, ?)',
                [$scope, $scopeId, $configPath, $valueConfig]
            );
        }
        $output->writeln('<comment>' . $configPath . '</comment> => <comment>' . $valueConfig . '</comment>');
        return $this;
    }

    /**
     * @param string $envName
     * @return array
     */
    protected function readConfig(string $envName): array
    {
        $baseValues = Yaml::parseFile($this->rootPath . 'configs/storeConfigData/base.yml');
        $envValues = Yaml::parseFile($this->rootPath . "configs/storeConfigData/{$envName}.yml");

        $rootSections = ['config', 'sql_query', 'magerun2_run'];

        foreach ($rootSections as $sectionName) {
            $baseValues[$sectionName] = $baseValues[$sectionName] ?? [];
            $envValues[$sectionName] = $envValues[$sectionName] ?? [];
        }

        return $this->mergeConfigs($baseValues, $envValues);
    }

    /**
     * Recursive merge array
     *
     * @param array $configs
     * @return array
     */
    private function mergeConfigs(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeConfigs($merged[$key], $value);
            } else if (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
