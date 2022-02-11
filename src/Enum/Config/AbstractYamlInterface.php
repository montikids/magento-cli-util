<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum\Config;

/**
 * Describes the most common traits applicable to all Yaml configs used in the project
 */
interface AbstractYamlInterface
{
    public const SECTION_SQL_QUERY = 'sql_query';
    public const SECTION_N98_MAGERUN2_COMMAND = 'n98_magerun2_command';
}
