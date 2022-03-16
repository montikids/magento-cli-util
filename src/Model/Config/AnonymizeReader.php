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
}
