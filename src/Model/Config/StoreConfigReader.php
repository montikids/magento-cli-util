<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Config;

use Montikids\MagentoCliUtil\Enum\Config\StoreConfigInterface;
use Montikids\MagentoCliUtil\Enum\FileDirInterface;

/**
 * Magento store config environment config reader
 */
class StoreConfigReader extends AbstractYamlReader
{
    protected const CONFIG_DIR_PATH = FileDirInterface::DIR_UTIL_CONFIG_STORE_CONFIG;
    protected const CONFIG_SECTIONS = StoreConfigInterface::ROOT_SECTIONS;
}
