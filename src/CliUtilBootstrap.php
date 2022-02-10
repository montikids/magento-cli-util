<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil;

use ErrorException;

/**
 * Bootstraps @see CliUtil
 */
class CliUtilBootstrap
{
    /**
     * Possible paths of the class loader
     */
    private const AUTOLOAD_PATHS = [
        __DIR__ . '/../../vendor/autoload.php',
        __DIR__ . '/../../../../autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../../../autoload.php',
    ];

    /**
     * @return CliUtil
     * @throws ErrorException
     */
    public static function createAndRunApplication(): CliUtil
    {
        static::registerClassLoader();

        $result = new CliUtil();
        $result->run();

        return $result;
    }

    /**
     * @return void
     * @throws ErrorException
     */
    public static function registerClassLoader(): void
    {
        $isLoaded = false;

        foreach (self::AUTOLOAD_PATHS as $path) {
            if (true === file_exists($path)) {
                require_once $path;

                $isLoaded = true;

                break;
            }
        }

        if (false === $isLoaded) {
            throw new ErrorException(
                'You must set up the project dependencies first. Run the "composer install" command.'
            );
        }
    }
}
