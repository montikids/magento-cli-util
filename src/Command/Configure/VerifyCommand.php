<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Command\Configure;

use Montikids\MagentoCliUtil\Command\AbstractCommand;
use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Command\Step\StepVerifyConfigs;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks whether the configuration files for currently selected environment are valid
 */
class VerifyCommand extends AbstractCommand
{
    use OutputFormatTrait;

    public const NAME = 'configure:verify';
    public const DESCRIPTION = 'Check configuration files are valid';

    /**
     * Expected command argument names
     */
    public const ARGUMENT_ENV = 'env';

    /**
     * @var StepVerifyConfigs
     */
    private $verifyConfigs;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        parent::__construct();

        $this->verifyConfigs = new StepVerifyConfigs();
    }

    /**
     * @return string
     */
    protected function getHelpInformation(): string
    {
        $result = <<<TEXT
By default, the check is performed for environment you selected before. 
You also can specify environment explicitly, as the first argument.
TEXT;

        return $result;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $allowedEnvs = implode(', ', EnvFileInterface::ALLOWED_VALUES_MK_CLI_UTIL_ENVIRONMENT);
        $this->addArgument(
            static::ARGUMENT_ENV,
            InputArgument::OPTIONAL,
            "Environment type. Possible values: {$allowedEnvs}."
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->wrapExecute(function () use ($input, $output) {
            $envType = $input->getArgument(self::ARGUMENT_ENV) ?? $this->getConfiguredEnvironmentType();
            $this->validateEnvWithException($envType);

            $this->printPrimary("Verifying configs for environment '{$envType}'...", $output);
            $this->verifyConfigs->execute($envType, $output);
        }, $output);

        return $result;
    }
}
