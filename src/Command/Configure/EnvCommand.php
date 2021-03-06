<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Command\Configure;

use Montikids\MagentoCliUtil\Command\AbstractCommand;
use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Command\Step\StepCopyEnvironmentFiles;
use Montikids\MagentoCliUtil\Model\Command\Step\StepSetEnvironment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sets up the specified environment for the util
 */
class EnvCommand extends AbstractCommand
{
    use OutputFormatTrait;

    public const NAME = 'configure:env';
    public const DESCRIPTION = 'Set the util environment type. Different environments use different config files.';

    /**
     * Expected command argument names
     */
    public const ARGUMENT_ENV = 'env';

    /**
     * @var StepSetEnvironment
     */
    private $setEnvironment;

    /**
     * @var StepCopyEnvironmentFiles
     */
    private $copyFiles;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        parent::__construct();

        $this->setEnvironment = new StepSetEnvironment();
        $this->copyFiles = new StepCopyEnvironmentFiles();
    }

    /**
     * @inheritDoc
     */
    protected function getHelpInformation(): string
    {
        $result = <<<TEXT
You have to select an enviroment type you're going to use. It allows to load the corresponding config file and
to avoid applying incorrect values or running non-expected commands.
The command also copies config samples and pre-creates configs (if not exist yet) for the specified environment.
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
            InputArgument::REQUIRED,
            "Environment type. Possible values: {$allowedEnvs}."
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = static::RESULT_CODE_SUCCESS;

        try {
            $envType = $input->getArgument(self::ARGUMENT_ENV);

            $this->validateEnvWithException($envType);
            $this->setEnvironment->execute($envType, $output);
            $this->copyFiles->execute($envType, $output);
        } catch (\Throwable $t) {
            $result = static::RESULT_CODE_ERROR;

            $this->printError($t->getMessage(), $output);
        }

        return $result;
    }
}
