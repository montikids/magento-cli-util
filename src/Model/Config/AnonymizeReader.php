<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Config;

use Montikids\MagentoCliUtil\Enum\Config\AnonymizeInterface;
use Montikids\MagentoCliUtil\Enum\FileDirInterface;

/**
 * Magento table anonymize environment config reader
 */
class AnonymizeReader extends AbstractYamlReader
{
    protected const CONFIG_DIR_PATH = FileDirInterface::DIR_UTIL_CONFIG_ANONYMIZE;
    protected const CONFIG_SECTIONS = AnonymizeInterface::ROOT_SECTIONS;

    /**
     * Applies variables to the @param string $path
     *
     * @return array
     *@see AnonymizeInterface::SECTION_CONFIG section
     *
     */
    protected function parseYamlFile(string $path): array
    {
        $result = parent::parseYamlFile($path);

        $config = $result[AnonymizeInterface::SECTION_CONFIG] ?? [];
        $variables = $result[AnonymizeInterface::SECTION_VARIABLE] ?? [];

        foreach ($config as &$table) {
            foreach ($table as &$columnConfig) {
                foreach ($columnConfig as &$option) {
                    $option = $variables[$option] ?? $option;
                }
            }
        }

        $result[AnonymizeInterface::SECTION_CONFIG] = $config;

        return $result;
    }
}
