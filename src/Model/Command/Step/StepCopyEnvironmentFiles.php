<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\FileDirInterface;
use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Copies sample config files and other related files
 * Always copies and rewrites all samples but the specified environment config is copied only if it doesn't exist
 */
class StepCopyEnvironmentFiles
{
    use OutputFormatTrait;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $envType
     * @param OutputInterface $output
     * @return void
     */
    public function execute(string $envType, OutputInterface $output): void
    {
        $this->printTitle("Copying files...", $output);
        $this->copyBasicFiles($output);
        $this->copyEnvFiles($envType, $output);
    }

    /**
     * Copy samples and other important files
     *
     * @param OutputInterface $output
     * @return void
     */
    private function copyBasicFiles(OutputInterface $output): void
    {
        $samplesSrc = FileDirInterface::DIR_PKG_CONFIG_SAMPLE;
        $samplesDst = FileDirInterface::DIR_UTIL_CONFIG_SAMPLE;
        $gitIgnoreSrc = FileDirInterface::DIR_PKG_CONFIG . '/' . FileDirInterface::FILE_NAME_GITIGNORE_SAMPLE;
        $gitIgnoreDst = FileDirInterface::DIR_UTIL_CONFIG . '/' . FileDirInterface::FILE_NAME_GITIGNORE;

        $baseConfig1Src = sprintf(
            '%s/%s',
            FileDirInterface::DIR_PKG_CONFIG_SAMPLE_ANONYMIZE,
            FileDirInterface::FILE_NAME_CONFIG_BASE
        );
        $baseConfig1Dst = sprintf(
            '%s/%s',
            FileDirInterface::DIR_UTIL_CONFIG_ANONYMIZE,
            FileDirInterface::FILE_NAME_CONFIG_BASE
        );

        $baseConfig2Src = sprintf(
            '%s/%s',
            FileDirInterface::DIR_PKG_CONFIG_SAMPLE_STORE_CONFIG,
            FileDirInterface::FILE_NAME_CONFIG_BASE
        );
        $baseConfig2Dst = sprintf(
            '%s/%s',
            FileDirInterface::DIR_UTIL_CONFIG_STORE_CONFIG,
            FileDirInterface::FILE_NAME_CONFIG_BASE
        );

        $this->filesystem->mirror($samplesSrc, $samplesDst, null, ['override' => true, 'copy_on_windows' => true]);
        $this->printCopyResult($samplesDst, false, $output);

        if (false === $this->filesystem->exists($gitIgnoreDst)) {
            $this->filesystem->copy($gitIgnoreSrc, $gitIgnoreDst);
            $this->printCopyResult($gitIgnoreDst, true, $output);
        } else {
            $this->printSkipResult($gitIgnoreDst, $output);
        }

        if (false === $this->filesystem->exists($baseConfig1Dst)) {
            $this->filesystem->copy($baseConfig1Src, $baseConfig1Dst);
            $this->printCopyResult($baseConfig1Dst, true, $output);
        } else {
            $this->printSkipResult($baseConfig1Dst, $output);
        }

        if (false === $this->filesystem->exists($baseConfig2Dst)) {
            $this->filesystem->copy($baseConfig2Src, $baseConfig2Dst);
            $this->printCopyResult($baseConfig2Dst, true, $output);
        } else {
            $this->printSkipResult($baseConfig2Dst, $output);
        }
    }

    /**
     * Copy environment-specific files
     *
     * @param string $envType
     * @param OutputInterface $output
     * @return void
     */
    private function copyEnvFiles(string $envType, OutputInterface $output): void
    {
        $isUnderVcs = ($envType !== EnvFileInterface::MK_CLI_UTIL_ENVIRONMENT_LOCAL);

        $envConfig1Src = sprintf(
            '%s/%s',
            FileDirInterface::DIR_PKG_CONFIG_SAMPLE_ANONYMIZE,
            "{$envType}.yml"
        );
        $envConfig1Dst = sprintf(
            '%s/%s',
            FileDirInterface::DIR_UTIL_CONFIG_ANONYMIZE,
            "{$envType}.yml"
        );

        $envConfig2Src = sprintf(
            '%s/%s',
            FileDirInterface::DIR_PKG_CONFIG_SAMPLE_STORE_CONFIG,
            "{$envType}.yml"
        );
        $envConfig2Dst = sprintf(
            '%s/%s',
            FileDirInterface::DIR_UTIL_CONFIG_STORE_CONFIG,
            "{$envType}.yml"
        );

        if (false === $this->filesystem->exists($envConfig1Dst)) {
            $this->filesystem->copy($envConfig1Src, $envConfig1Dst);
            $this->printCopyResult($envConfig1Dst, $isUnderVcs, $output);
        } else {
            $this->printSkipResult($envConfig1Dst, $output);
        }

        if (false === $this->filesystem->exists($envConfig2Dst)) {
            $this->filesystem->copy($envConfig2Src, $envConfig2Dst);
            $this->printCopyResult($envConfig2Dst, $isUnderVcs, $output);
        } else {
            $this->printSkipResult($envConfig2Dst, $output);
        }
    }

    /**
     * @param string $destinationPath
     * @param bool $isUnderVcs
     * @param OutputInterface $output
     * @return void
     */
    private function printCopyResult(string $destinationPath, bool $isUnderVcs, OutputInterface $output): void
    {
        $relativePath = $this->filesystem->makePathRelative($destinationPath, FileDirInterface::DIR_MAGENTO_ROOT);
        $relativePath = rtrim($relativePath, '/');

        $this->printPrimary("- CREATED/UPDATED: ($relativePath)", $output);

        if (true === $isUnderVcs) {
            $this->printSecondary("Don't forget to add the file to the repository!", $output);
        } else {
            $this->printSecondary("Is ignored by VCS by default. Feel free to change it.", $output);
        }

        $this->printSeparatorSmall($output);
    }

    /**
     * @param string $destinationPath
     * @param OutputInterface $output
     * @return void
     */
    private function printSkipResult(string $destinationPath, OutputInterface $output): void
    {
        $relativePath = $this->filesystem->makePathRelative($destinationPath, FileDirInterface::DIR_MAGENTO_ROOT);
        $relativePath = rtrim($relativePath, '/');

        $this->printSecondary("- SKIPPED (already exists): ($relativePath)", $output);
        $this->printSeparatorSmall($output);
    }
}
