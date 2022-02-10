<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Config;

use Montikids\MagentoCliUtil\Enum\Config\StoreConfigInterface;

/**
 * Magento store config environment config reader
 */
class StoreConfigReader extends AbstractYamlReader
{
    /**
     * @var string
     */
    protected const CONFIG_DIR_PATH = StoreConfigInterface::PATH_DIR;
    protected const CONFIG_SECTIONS = StoreConfigInterface::ROOT_SECTIONS;
}
