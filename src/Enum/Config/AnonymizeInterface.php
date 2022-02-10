<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum\Config;

/**
 * TODO: Add description
 */
interface AnonymizeInterface extends AbstractYamlInterface
{
    /**
     * @deprecated
     */
    public const PATH_DIR = 'config/anonymize';

    public const SECTION_VARIABLE = 'variable';

    public const ROOT_SECTIONS = [
        self::SECTION_CONFIG,
        self::SECTION_SQL_QUERY,
        self::SECTION_N98_MAGERUN2_COMMAND,
    ];
}
