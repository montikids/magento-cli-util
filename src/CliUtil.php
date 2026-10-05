<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil;

use Montikids\MagentoCliUtil\Command\Configure\EnvCommand;
use Montikids\MagentoCliUtil\Command\Configure\VerifyCommand;
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
    private const APP_VERSION = '1.0.9';

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
        $commands = [
            new ApplyConfigCommand(),
            new AnonymizeCommand(),
            new EnvCommand(),
            new VerifyCommand(),
        ];

        // Symfony Console 7.4 deprecated add() in favor of addCommand(), 8.0 removed add()
        $method = method_exists($this, 'addCommand') ? 'addCommand' : 'add';

        foreach ($commands as $command) {
            $this->$method($command);
        }
    }
}
