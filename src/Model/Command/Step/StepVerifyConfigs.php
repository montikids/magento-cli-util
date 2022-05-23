<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Config\AnonymizeReader;
use Montikids\MagentoCliUtil\Model\Config\StoreConfigReader;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Verifies configuration files are valid
 */
class StepVerifyConfigs
{
    use OutputFormatTrait;

    /**
     * @var StoreConfigReader
     */
    private $storeConfigReader;

    /**
     * @var AnonymizeReader
     */
    private $anonConfigReader;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        $this->storeConfigReader = new StoreConfigReader();
        $this->anonConfigReader = new AnonymizeReader();
    }

    /**
     * @param string $environment
     * @param OutputInterface $output
     * @return void
     */
    public function execute(string $environment, OutputInterface $output): void
    {
        $this->checkStoreConfigs($environment, $output);
        $this->checkAnonymizationConfigs($environment, $output);
    }

    /**
     * @param string $environment
     * @param OutputInterface $output
     * @return void
     */
    private function checkStoreConfigs(string $environment, OutputInterface $output): void
    {
        $this->printTitle("Checking store config files", $output);

        $this->printSecondary('Base config...', $output);
        $this->storeConfigReader->readBaseConfig();
        $this->printPrimary('OK', $output);

        $this->printSecondary("Env ({$environment}) config...", $output);
        $this->storeConfigReader->readEnvConfig($environment);
        $this->printPrimary('OK', $output);

        $this->printSecondary('Local config...', $output);
        $this->storeConfigReader->readLocalConfig();
        $this->printPrimary('OK', $output);

        $this->printSecondary('Merged config...', $output);
        $this->storeConfigReader->readMergedConfig($environment);
        $this->printPrimary('OK', $output);
    }

    /**
     * @param string $environment
     * @param OutputInterface $output
     * @return void
     */
    private function checkAnonymizationConfigs(string $environment, OutputInterface $output): void
    {
        $this->printTitle("Checking anonymization config files", $output);

        $this->printSecondary('Base config...', $output);
        $this->anonConfigReader->readBaseConfig();
        $this->printPrimary('OK', $output);

        $this->printSecondary("Env ({$environment}) config...", $output);
        $this->anonConfigReader->readEnvConfig($environment);
        $this->printPrimary('OK', $output);

        $this->printSecondary('Local config...', $output);
        $this->anonConfigReader->readLocalConfig();
        $this->printPrimary('OK', $output);

        $this->printSecondary('Merged config...', $output);
        $this->anonConfigReader->readMergedConfig($environment);
        $this->printPrimary('OK', $output);
    }
}
