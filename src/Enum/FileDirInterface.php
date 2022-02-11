<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum;

/**
 * List of some directories and files relative paths
 * All paths are relative to the vendor/bin because that's teh place where the main executable file located
 */
interface FileDirInterface
{
    /**
     * Global paths
     * These paths are based on global constants defined during executing the main "binary" file (bin/mk-cli-util)
     */
    public const DIR_BIN = MK_CLI_UTIL_BIN_DIR;
    public const DIR_VENDOR_ROOT = MK_VENDOR_ROOT;
    public const DIR_MAGENTO_ROOT = MK_MAGENTO_ROOT;

    /**
     * Magento paths
     */
    public const DIR_MAGENTO_APP = self::DIR_MAGENTO_ROOT . '/app';
    public const DIR_MAGENTO_APP_ETC = self::DIR_MAGENTO_APP . '/etc';
    public const DIR_VENDOR_BIN = self::DIR_VENDOR_ROOT . '/bin';

    /**
     * Package paths
     */
    public const DIR_PKG_ROOT = self::DIR_BIN . '/..';
    public const DIR_PKG_CONFIG = self::DIR_PKG_ROOT . '/config';
    public const DIR_PKG_CONFIG_SAMPLE = self::DIR_PKG_CONFIG . '/_sample';
    public const DIR_PKG_CONFIG_SAMPLE_ANONYMIZE = self::DIR_PKG_CONFIG_SAMPLE . '/anonymize';
    public const DIR_PKG_CONFIG_SAMPLE_STORE_CONFIG = self::DIR_PKG_CONFIG_SAMPLE . '/store-config';

    public const DIR_UTIL_ROOT = self::DIR_MAGENTO_ROOT . '/mk-cli-util';
    public const DIR_UTIL_CONFIG = self::DIR_UTIL_ROOT . '/config';
    public const DIR_UTIL_CONFIG_SAMPLE = self::DIR_UTIL_CONFIG . '/sample';
    public const DIR_UTIL_CONFIG_ANONYMIZE = self::DIR_UTIL_CONFIG . '/anonymize';
    public const DIR_UTIL_CONFIG_STORE_CONFIG = self::DIR_UTIL_CONFIG . '/store-config';

    /**
     * Some important file names
     */
    public const FILE_NAME_CONFIG_BASE = 'base.yml';
    public const FILE_NAME_GITIGNORE_SAMPLE = '.gitignore_sample';
    public const FILE_NAME_GITIGNORE = '.gitignore';
    public const FILE_NAME_ENV = 'env.php';
}
