<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Enum;

/**
 * List of available N98 Magerun 2 commands
 */
interface N98CommandInterface
{
    public const ALL_COMMANDS = [
        self::CONFIG_ENV_SHOW,
        self::CONFIG_ENV_SET,
        self::CACHE_CLEAN,
    ];

    public const CONFIG_ENV_SHOW = 'config:env:show';
    public const CONFIG_ENV_SET = 'config:env:set';

    public const CACHE_CLEAN = 'cache:clean';
}
