<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum\Magento;

use Montikids\MagentoCliUtil\Enum\FileDirInterface;

/**
 * Describes Magento env file structure and values we use
 */
interface EnvFileInterface
{
    public const FILE_PATH = FileDirInterface::DIR_MAGENTO_APP_ETC . '/' . FileDirInterface::FILE_NAME_ENV;

    public const MK_CLI_UTIL_ENVIRONMENT_LOCAL = 'local';
    public const MK_CLI_UTIL_ENVIRONMENT_DEV = 'dev';
    public const MK_CLI_UTIL_ENVIRONMENT_STAGE = 'stage';

    public const MK_CLI_UTIL_ENVIRONMENT_ALLOWED_VALUES = [
        self::MK_CLI_UTIL_ENVIRONMENT_LOCAL,
        self::MK_CLI_UTIL_ENVIRONMENT_DEV,
        self::MK_CLI_UTIL_ENVIRONMENT_STAGE,
    ];

    public const CRYPT_KEY = 'crypt.key';
    public const DB_TABLE_PREFIX = 'db.table_prefix';
    public const DB_CONNECTION_DEFAULT_HOST = 'db.connection.default.host';
    public const DB_CONNECTION_DEFAULT_USERNAME = 'db.connection.default.username';
    public const DB_CONNECTION_DEFAULT_PASSWORD = 'db.connection.default.password';
    public const DB_CONNECTION_DEFAULT_DBNAME = 'db.connection.default.dbname';
    public const MK_CLI_UTIL_ENVIRONMENT = 'mk_cli_util.environment';
}
