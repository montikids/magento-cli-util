<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum\Magento;

/**
 * TODO: Add description
 */
interface EnvFileInterface
{
    public const FILE_PATH = 'app/etc/env.php';

    public const DB_CONNECTION_DEFAULT_HOST = 'db.connection.default.host';
    public const DB_CONNECTION_DEFAULT_USERNAME = 'db.connection.default.username';
    public const DB_CONNECTION_DEFAULT_PASSWORD = 'db.connection.default.password';
    public const DB_CONNECTION_DEFAULT_DBNAME = 'db.connection.default.dbname';

    public const CRYPT_KEY = 'crypt.key';

    public const MK_CLI_UTIL_ENVIRONMENT = 'mk_cli_util.environment';
    public const MK_CLI_UTIL_ENVIRONMENT_ALLOWED_VALUES = [
        'local',
        'dev',
        'stage',
    ];
}
