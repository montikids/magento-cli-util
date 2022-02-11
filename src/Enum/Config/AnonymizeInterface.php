<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum\Config;

/**
 * Describes Magento tables anonymization config structure
 */
interface AnonymizeInterface extends AbstractYamlInterface
{
    public const SECTION_TABLES = 'tables';

    public const FIELD_OPTION_VALUE = 'value';
    public const FILED_OPTION_FIELD_TO_CONCAT = 'field_to_concat';
    public const FIELD_OPTION_POSTFIX = 'postfix';
    public const FIELD_OPTION_IS_PASSWORD = 'is_password';
    public const FIELD_OPTION_CONCAT_FIELD_NAME = 'concat_field_name';

    public const ROOT_SECTIONS = [
        self::SECTION_TABLES,
        self::SECTION_SQL_QUERY,
        self::SECTION_N98_MAGERUN2_COMMAND,
    ];

    public const FIELD_OPTIONS = [
        self::FIELD_OPTION_VALUE,
        self::FILED_OPTION_FIELD_TO_CONCAT,
        self::FIELD_OPTION_POSTFIX,
        self::FIELD_OPTION_IS_PASSWORD,
        self::FIELD_OPTION_CONCAT_FIELD_NAME,
    ];
}
