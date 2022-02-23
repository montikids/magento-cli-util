<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil;

use Montikids\MagentoCliUtil\Command\Configure\EnvCommand;
use Montikids\MagentoCliUtil\Command\Db\AnonymizeCommand;
use Montikids\MagentoCliUtil\Command\Db\ApplyConfigCommand;
use Symfony\Component\Console\Application;

/**
 * CLI util application main class
 */
class CliUtil extends Application
{
    /**
     * @var string
     */
    private const APP_NAME = 'mk-cli-util';

    /**
     * @var string
     */
    private const APP_VERSION = 'dev-1.0.0';

    /**
     * Customized constructor
     */
    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);

        $this->initialize();
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        $this->registerCommands();
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        $this->add(new ApplyConfigCommand());
        $this->add(new AnonymizeCommand());
        $this->add(new EnvCommand());
    }
}
