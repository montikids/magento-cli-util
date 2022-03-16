<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Config;

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
     * Returns environment config merged with the base and the local ones
     * Config values priority is: local > env > base
     *
     * @param string $environment
     * @return array
     */
    public function readMergedConfig(string $environment): array
    {
        $baseConfig = $this->readBaseConfig();
        $envConfig = $this->readEnvConfig($environment);
        $localConfig = $this->readLocalConfig();

        $result = $this->mergeConfigs($baseConfig, $envConfig);
        $result = $this->mergeConfigs($result, $localConfig);

        return $result;
    }

    /**
     * Reads the base config that must be always present
     *
     * @return array
     */
    private function readBaseConfig(): array
    {
        $configPath = $this->getConfigPathForFile(FileDirInterface::FILE_NAME_CONFIG_BASE);
        $result = $this->parseYamlFile($configPath);

        foreach (static::CONFIG_SECTIONS as $sectionName) {
            if (false === array_key_exists($sectionName, $result)) {
                $result[$sectionName] = [];
            }
        }

        return $result;
    }

    /**
     * Reads the local config that is optional
     *
     * @return array
     */
    private function readLocalConfig(): array
    {
        $result = [];
        $configPath = $this->getConfigPathForFile(FileDirInterface::FILE_NAME_CONFIG_LOCAL);

        if (true === is_file($configPath)) {
            $result = $this->parseYamlFile($configPath);
        }

        foreach (static::CONFIG_SECTIONS as $sectionName) {
            if (false === array_key_exists($sectionName, $result)) {
                $result[$sectionName] = [];
            }
        }

        return $result;
    }

    /**
     * Reads config for the specified environment that is optional
     *
     * @param string $environment
     * @return array
     */
    private function readEnvConfig(string $environment): array
    {
        $result = [];
        $configPath = $this->getEnvConfigPath($environment);

        if (true === is_file($configPath)) {
            $result = $this->parseYamlFile($configPath);
        }

        foreach (static::CONFIG_SECTIONS as $sectionName) {
            if (false === array_key_exists($sectionName, $result)) {
                $result[$sectionName] = [];
            }
        }

        return $result;
    }

    /**
     * @param string $scopeName
     * @return string
     */
    private function getEnvConfigPath(string $scopeName): string
    {
        $fileName = "{$scopeName}.yml";
        $pathParts = [
            static::CONFIG_DIR_PATH,
            $fileName,
        ];

        $result = implode('/', $pathParts);

        return $result;
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getConfigPathForFile(string $fileName): string
    {
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
    private function parseYamlFile(string $path): array
    {
        $result = Yaml::parseFile($path);

        return $result;
    }

    /**
     * @param array $baseConfig
     * @param array $envConfig
     * @return array
     */
    private function mergeConfigs(array $baseConfig, array $envConfig): array
    {
        $result = $baseConfig;

        foreach ($envConfig as $key => $envValue) {
            $baseValue = $baseConfig[$key] ?? null;
            $isEnvValueComplex = (true === is_array($envValue));
            $isBaseValueComplex = (true === is_array($baseValue));

            if ((true === $isEnvValueComplex) && (true === $isBaseValueComplex)) {
                $result[$key] = $this->mergeConfigs($baseValue, $envValue);
            } elseif (true === is_numeric($key)) {
                $notSetInBase = (false === in_array($envValue, $baseConfig));

                if (true === $notSetInBase) {
                    $result[] = $envValue;
                }
            } else {
                $result[$key] = $envValue;
            }
        }

        return $result;
    }
}
