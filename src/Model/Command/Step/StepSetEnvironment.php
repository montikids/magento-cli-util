<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\FileDirInterface;
use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Enum\N98CommandInterface;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Service\RunN98Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Sets the util environment variable in Magento config
 */
class StepSetEnvironment
{
    use OutputFormatTrait;

    /**
     * @var RunN98Command
     */
    private $n98;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        $this->n98 = new RunN98Command();
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $envToSet
     * @param OutputInterface $output
     * @return void
     */
    public function execute(string $envToSet, OutputInterface $output): void
    {
        $configPath = EnvFileInterface::PATH_MK_CLI_UTIL_ENVIRONMENT;
        $filePath = $this->getRelativePath(EnvFileInterface::FILE_PATH);

        $this->printTitle("Setting environment variable ('{$configPath}' => '{$filePath}')...", $output);

        $executionResult = $this->n98->execute(N98CommandInterface::CONFIG_ENV_SET, [$configPath, $envToSet]);
        $this->printPrimary($executionResult, $output);
    }

    /**
     * @param string $destinationPath
     * @return string
     */
    private function getRelativePath(string $destinationPath): string
    {
        $result = $this->filesystem->makePathRelative($destinationPath, FileDirInterface::DIR_MAGENTO_ROOT);
        $result = rtrim($result, '/');

        return $result;
    }
}
