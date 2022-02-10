<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum\Config;

/**
 * TODO: Add description
 */
interface StoreConfigInterface extends AbstractYamlInterface
{
    public const PATH_DIR = 'config/store-config';

    public const ROOT_SECTIONS = [
        self::SECTION_CONFIG,
        self::SECTION_SQL_QUERY,
        self::SECTION_N98_MAGERUN2_COMMAND,
    ];

    public const OPTION_ENCRYPT = 'encrypt';
    public const OPTION_DELETE = 'delete';
    public const OPTION_SKIP = 'skip';
}
