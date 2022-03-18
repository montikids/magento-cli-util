<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\FileDirInterface;
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
     * @var string[]
     */
    private $newConfigs = [];

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

        $this->printNewConfigsWarning($output);
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
        $gitIgnoreSrc = FileDirInterface::DIR_PKG_ROOT . '/' . FileDirInterface::FILE_NAME_GITIGNORE_SAMPLE;
        $gitIgnoreDst = FileDirInterface::DIR_UTIL_ROOT . '/' . FileDirInterface::FILE_NAME_GITIGNORE;
        $readmeSrc = FileDirInterface::DIR_PKG_ROOT . '/' . FileDirInterface::FILE_NAME_README;
        $readmeDst = FileDirInterface::DIR_UTIL_ROOT . '/' . FileDirInterface::FILE_NAME_README;

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

        $localConfig1Src = sprintf(
            '%s/%s',
            FileDirInterface::DIR_PKG_CONFIG_SAMPLE_ANONYMIZE,
            FileDirInterface::FILE_NAME_CONFIG_LOCAL
        );
        $localConfig1Dst = sprintf(
            '%s/%s',
            FileDirInterface::DIR_UTIL_CONFIG_ANONYMIZE,
            FileDirInterface::FILE_NAME_CONFIG_LOCAL
        );

        $localConfig2Src = sprintf(
            '%s/%s',
            FileDirInterface::DIR_PKG_CONFIG_SAMPLE_STORE_CONFIG,
            FileDirInterface::FILE_NAME_CONFIG_LOCAL
        );
        $localConfig2Dst = sprintf(
            '%s/%s',
            FileDirInterface::DIR_UTIL_CONFIG_STORE_CONFIG,
            FileDirInterface::FILE_NAME_CONFIG_LOCAL
        );

        $this->filesystem->mirror($samplesSrc, $samplesDst, null, ['override' => true, 'copy_on_windows' => true]);
        $this->printCopyResult($samplesDst, false, $output);

        $this->copyFileIfNotExist($gitIgnoreSrc, $gitIgnoreDst, true, $output);

        $this->filesystem->copy($readmeSrc, $readmeDst);
        $this->printCopyResult($readmeDst, false, $output);

        if (true === $this->copyFileIfNotExist($baseConfig1Src, $baseConfig1Dst, true, $output)) {
            $this->newConfigs[] = $baseConfig1Dst;
        }

        if (true === $this->copyFileIfNotExist($baseConfig1Src, $baseConfig1Dst, true, $output)) {
            $this->newConfigs[] = $baseConfig1Dst;
        }

        if (true === $this->copyFileIfNotExist($baseConfig2Src, $baseConfig2Dst, true, $output)) {
            $this->newConfigs[] = $baseConfig2Dst;
        }

        if (true === $this->copyFileIfNotExist($localConfig1Src, $localConfig1Dst, false, $output)) {
            $this->newConfigs[] = $localConfig1Dst;
        }

        if (true === $this->copyFileIfNotExist($localConfig2Src, $localConfig2Dst, false, $output)) {
            $this->newConfigs[] = $localConfig2Dst;
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

        if (true === $this->copyFileIfNotExist($envConfig1Src, $envConfig1Dst, true, $output)) {
            $this->newConfigs[] = $envConfig1Dst;
        }

        if (true === $this->copyFileIfNotExist($envConfig2Src, $envConfig2Dst, true, $output)) {
            $this->newConfigs[] = $envConfig2Dst;
        }
    }

    /**
     * @param string $src
     * @param string $dst
     * @param bool $underVcs
     * @param OutputInterface $output
     * @return bool
     */
    private function copyFileIfNotExist(string $src, string $dst, bool $underVcs, OutputInterface $output): bool
    {
        $result = false;

        if (false === $this->filesystem->exists($dst)) {
            $this->filesystem->copy($src, $dst);
            $this->printCopyResult($dst, $underVcs, $output);

            $result = true;
        } else {
            $this->printSkipResult($dst, $output);
        }

        return $result;
    }

    /**
     * @param string $destinationPath
     * @param bool $isUnderVcs
     * @param OutputInterface $output
     * @return void
     */
    private function printCopyResult(string $destinationPath, bool $isUnderVcs, OutputInterface $output): void
    {
        $relativePath = $this->getRelativePath($destinationPath);

        $this->printPrimary("- CREATED/UPDATED: {$relativePath}", $output);

        if (true === $isUnderVcs) {
            $this->printSecondary("Don't forget to commit the file to your repository!", $output);
        } else {
            $this->printSecondary("It's ignored by VCS by default. Feel free to change it.", $output);
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
        $relativePath = $this->getRelativePath($destinationPath);

        $this->printSecondary("- SKIPPED (already exists): {$relativePath}", $output);
        $this->printSeparatorSmall($output);
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    private function printNewConfigsWarning(OutputInterface $output): void
    {
        if (count($this->newConfigs) > 1) {
            $this->printPrimary("The following new configs were initialized:", $output);

            foreach ($this->newConfigs as $path) {
                $relativePath = $this->getRelativePath($path);
                $this->printSecondary("- {$relativePath}", $output);
            }

            $this->printError("That's only samples. Please, revise them and modify. Don't use them 'as is'!", $output);
        }
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
