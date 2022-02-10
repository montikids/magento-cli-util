<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Config;

use Montikids\MagentoCliUtil\Enum\Config\StoreConfigInterface;
use Montikids\MagentoCliUtil\Enum\FileDirInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Contains some common methods and information that helps to read Yaml configs for the specific environment
 */
abstract class AbstractYamlReader
{
    /**
     * Path to the die with the set of config files
     * The path is relative to the base dir
     * Must be overridden in children classes
     */
    protected const CONFIG_DIR_PATH = null;

    /**
     * Config sections that should be read
     * Must be overridden in children classes
     */
    protected const CONFIG_SECTIONS = null;

    /**
     * Returns environment config merged with the base one
     *
     * @param string $environment
     * @return array
     */
    public function readMergedConfig(string $environment): array
    {
        $baseConfig = $this->readBaseConfig();
        $envConfig = $this->readEnvConfig($environment);

        $result = $this->mergeConfigs($baseConfig, $envConfig);

        return $result;
    }

    /**
     * Reads the base config that must be always present
     *
     * @return array
     */
    public function readBaseConfig(): array
    {
        $configPath = $this->getConfigPath(null);
        $result = $this->parseYamlFile($configPath);

        foreach (StoreConfigInterface::ROOT_SECTIONS as $sectionName) {
            $result[$sectionName] = $result[$sectionName] ?? [];
        }

        return $result;
    }

    /**
     * Reads config for the specified environment which can be absent
     *
     * @param string $environment
     * @return array
     */
    public function readEnvConfig(string $environment): array
    {
        $result = [];

        $configPath = $this->getConfigPath($environment);

        if (true === is_file($configPath)) {
            $result = $this->parseYamlFile($configPath);
        }

        foreach (StoreConfigInterface::ROOT_SECTIONS as $sectionName) {
            $result[$sectionName] = $result[$sectionName] ?? [];
        }

        return $result;
    }

    /**
     * @param string|null $scopeName
     * @return string
     */
    protected function getConfigPath(?string $scopeName): string
    {
        $fileName = (null !== $scopeName) ? "{$scopeName}.yml" : FileDirInterface::FILE_NAME_CONFIG_BASE;
        $pathParts = [
            static::CONFIG_DIR_PATH,
            $fileName,
        ];

        $result = implode('/', $pathParts);

        return $result;
    }

    /**
     * @param string $path
     * @return array
     */
    protected function parseYamlFile(string $path): array
    {
        $result = Yaml::parseFile($path);

        return $result;
    }

    /**
     * @param array $config1
     * @param array $config2
     * @return array
     */
    private function mergeConfigs(array $config1, array $config2): array
    {
        $result = $config1;

        foreach ($config2 as $key => &$value) { // TODO
            if ((true === is_array($value)) && isset($result[$key]) && is_array($result[$key])) {
                $result[$key] = $this->mergeConfigs($result[$key], $value);
            } elseif (is_numeric($key)) {
                if (false === in_array($value, $result)) {
                    $result[] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
