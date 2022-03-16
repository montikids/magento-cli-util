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

    public const PATH_CRYPT_KEY = 'crypt.key';
    public const PATH_DB_TABLE_PREFIX = 'db.table_prefix';
    public const PATH_DB_CONNECTION_DEFAULT_HOST = 'db.connection.default.host';
    public const PATH_DB_CONNECTION_DEFAULT_USERNAME = 'db.connection.default.username';
    public const PATH_DB_CONNECTION_DEFAULT_PASSWORD = 'db.connection.default.password';
    public const PATH_DB_CONNECTION_DEFAULT_DBNAME = 'db.connection.default.dbname';
    public const PATH_MK_CLI_UTIL_ENVIRONMENT = 'mk_cli_util.environment';

    public const VALUE_MK_CLI_UTIL_ENVIRONMENT_DEV = 'dev';
    public const VALUE_MK_CLI_UTIL_ENVIRONMENT_STAGE = 'stage';

    public const ALLOWED_VALUES_MK_CLI_UTIL_ENVIRONMENT = [
        self::VALUE_MK_CLI_UTIL_ENVIRONMENT_DEV,
        self::VALUE_MK_CLI_UTIL_ENVIRONMENT_STAGE,
    ];
}
