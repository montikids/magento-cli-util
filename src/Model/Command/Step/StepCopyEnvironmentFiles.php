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
        $this->printTitle("Copying config samples...", $output);
        $this->copySamples($envType);
    }

    /**
     * @param string $envType
     * @return void
     */
    private function copySamples(string $envType): void
    {
        $samplesSrc = FileDirInterface::DIR_PKG_CONFIG_SAMPLE;
        $samplesDst = FileDirInterface::DIR_UTIL_CONFIG_SAMPLE;
        $this->filesystem->mirror($samplesSrc, $samplesDst, null, ['override' => true, 'copy_on_windows' => true]);

        $gitIgnoreSrc = FileDirInterface::DIR_PKG_CONFIG . '/' . FileDirInterface::FILE_NAME_GITIGNORE_SAMPLE;
        $gitIgnoreDst = FileDirInterface::DIR_UTIL_CONFIG . '/' . FileDirInterface::FILE_NAME_GITIGNORE;
        $this->filesystem->copy($gitIgnoreSrc, $gitIgnoreDst, true);

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

        $this->filesystem->copy($baseConfig1Src, $baseConfig1Dst, false);
        $this->filesystem->copy($baseConfig2Src, $baseConfig2Dst, false);
        $this->filesystem->copy($envConfig1Src, $envConfig1Dst, false);
        $this->filesystem->copy($envConfig2Src, $envConfig2Dst, false);
    }
}
