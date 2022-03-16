<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Command;

use Montikids\MagentoCliUtil\Command\Configure\EnvCommand;
use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Exception\InvalidEnvironmentException;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Magento\EnvFileReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract CLI tool command that contains the most common logic
 */
abstract class AbstractCommand extends Command
{
    use OutputFormatTrait;

    /**
     * Command name. Must be overridden in children classes.
     * @var string|null
     */
    public const NAME = null;

    /**
     * Command description. Must be overridden in children classes.
     * @var string|null
     */
    public const DESCRIPTION = null;

    /**
     * Exit codes
     */
    protected const RESULT_CODE_SUCCESS = 0;
    protected const RESULT_CODE_ERROR = 0;

    /**
     * @var EnvFileReader
     */
    protected $envFileReader;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        parent::__construct();

        $this->envFileReader = new EnvFileReader();
    }

    /**
     * Command's help
     *
     * @return string
     */
    abstract protected function getHelpInformation(): string;

    /**
     * The ultimate configuration for most commands
     * @return void
     */
    protected function configure(): void
    {
        $helpInfo = $this->getHelpInformation();

        $this->setName(static::NAME);
        $this->setDescription(static::DESCRIPTION);
        $this->setHelp($helpInfo);
    }

    /**
     * Wraps the main logic in order to unify catching exceptions and minify repeating the code
     *
     * @param \Closure $execute
     * @param OutputInterface $output
     * @return int
     */
    protected function wrapExecute(\Closure $execute, OutputInterface $output): int
    {
        $result = static::SUCCESS;

        try {
            $execute();
        } catch (\Throwable $t) {
            $result = static::RESULT_CODE_ERROR;
            $this->printException($t, $output);
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getConfiguredEnvironmentType(): string
    {
        $setValue = $this->envFileReader->readStringValue(EnvFileInterface::PATH_MK_CLI_UTIL_ENVIRONMENT);

        if ('' === $setValue) {
            $error = 'Environment is not configured yet. Run ' . EnvCommand::NAME . ' command first.';
        } elseif (false === $this->validateEnv($setValue)) {
            $error = sprintf(
                'Your configured an unsupported environment type. Please check %s (%s). Allowed environments: (%s).',
                EnvFileInterface::FILE_PATH,
                EnvFileInterface::PATH_MK_CLI_UTIL_ENVIRONMENT,
                $this->getAllowedEnvsString()
            );
        } else {
            $error = null;
        }

        if (null !== $error) {
            throw new InvalidEnvironmentException($error);
        }

        return $setValue;
    }

    /**
     * @param string $envType
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateEnvWithException(string $envType): void
    {
        $isAllowed = $this->validateEnv($envType);

        if (false === $isAllowed) {
            $allowedEnvsStr = $this->getAllowedEnvsString();
            $error = "Environment '$envType' is not valid. Allowed environment types: $allowedEnvsStr";

            throw new \InvalidArgumentException($error);
        }
    }

    /**
     * @param string $envType
     * @return bool
     */
    protected function validateEnv(string $envType): bool
    {
        $result = in_array($envType, EnvFileInterface::ALLOWED_VALUES_MK_CLI_UTIL_ENVIRONMENT);

        return $result;
    }

    /**
     * @return string
     */
    protected function getAllowedEnvsString(): string
    {
        $allowedEnvs = EnvFileInterface::ALLOWED_VALUES_MK_CLI_UTIL_ENVIRONMENT;
        $result = implode(', ', $allowedEnvs);

        return $result;
    }
}
