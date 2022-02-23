<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\Config\StoreConfigInterface;
use Montikids\MagentoCliUtil\Exception\InvalidConfigException;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Magento\ConfigTableWriter;
use Montikids\MagentoCliUtil\Model\Magento\ValueEncryptor;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Applies config values to the 'core_config_data' Magento DB table
 */
class StepApplyConfig
{
    use OutputFormatTrait;

    /**
     * @var ConfigTableWriter
     */
    private $configWriter;

    /**
     * @var ValueEncryptor
     */
    private $configValueEnc;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        $this->configWriter = new ConfigTableWriter();
        $this->configValueEnc = new ValueEncryptor();
    }

    /**
     * @param array $config
     * @param OutputInterface $output
     * @return void
     * @throws InvalidConfigException
     * @throws \SodiumException
     */
    public function execute(array $config, OutputInterface $output): void
    {
        $storeConfigValues = $config[StoreConfigInterface::SECTION_CONFIG] ?? [];

        if (false === empty($storeConfigValues)) {
            $this->printTitle("Updating 'core_config_data' table values...", $output);

            foreach ($storeConfigValues as $scopeType => $values) {
                $this->applyScopeTypeConfigValues($scopeType, $values, $output);
            }
        } else {
            $this->printError("No config values to apply. Please, check the config.", $output);
        }
    }

    /**
     * @param string $scopeType
     * @param array $areaConfig
     * @param OutputInterface $output
     * @return void
     * @throws InvalidConfigException
     * @throws \SodiumException
     */
    private function applyScopeTypeConfigValues(string $scopeType, array $areaConfig, OutputInterface $output): void
    {
        foreach ($areaConfig as $scopeId => $values) {
            $this->applyScopeConfigValues($scopeType, $scopeId, $values, $output);
        }
    }

    /**
     * @param string $scopeType
     * @param int $scopeId
     * @param array $scopeConfig
     * @param OutputInterface $output
     * @return void
     * @throws InvalidConfigException
     * @throws \SodiumException
     */
    private function applyScopeConfigValues(
        string $scopeType,
        int $scopeId,
        array $scopeConfig,
        OutputInterface $output
    ): void {
        foreach ($scopeConfig as $path => $value) {
            if (true === is_array($value)) {
                $this->processConfigValueWithOptions($path, $scopeType, $scopeId, $value, $output);
            } else {
                $strNullValue = (null !== $value) ? (string)$value : null;
                $this->setConfigValue($path, $scopeType, $scopeId, $strNullValue, $output);
            }
        }
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param int $scopeId
     * @param array $valueOptions
     * @param OutputInterface $output
     * @return void
     * @throws InvalidConfigException
     * @throws \SodiumException
     */
    private function processConfigValueWithOptions(
        string $path,
        string $scopeType,
        int $scopeId,
        array $valueOptions,
        OutputInterface $output
    ): void {
        foreach ($valueOptions as $type => $value) {
            $stringValue = (string)$value;
            $this->processConfigValueOption($path, $scopeType, $scopeId, $type, $stringValue, $output);
        }
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param int $scopeId
     * @param string $optionType
     * @param string|null $value
     * @param OutputInterface $output
     * @return void
     * @throws InvalidConfigException
     * @throws \SodiumException
     */
    private function processConfigValueOption(
        string $path,
        string $scopeType,
        int $scopeId,
        string $optionType,
        ?string $value,
        OutputInterface $output
    ): void {
        switch ($optionType) {
            case StoreConfigInterface::OPTION_SKIP:
                $this->printSecondary("SKIPPED: {$path} ({$scopeType}, {$scopeId})", $output);
                break;

            case StoreConfigInterface::OPTION_DELETE:
                $this->deleteConfigValue($path, $scopeType, $scopeId, $output);
                break;

            case StoreConfigInterface::OPTION_ENCRYPT:
                $encryptedValue = $this->configValueEnc->encrypt($value);
                $this->setConfigValue($path, $scopeType, $scopeId, $encryptedValue, $output);
                break;

            default:
                $error = "Value option {$optionType} is not recognized";

                throw new InvalidConfigException($error);
        }
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    private function deleteConfigValue(
        string $path,
        string $scope,
        int $scopeId,
        OutputInterface $output
    ): void {
        $this->configWriter->deleteValue($path, $scope, $scopeId);
        $this->printError("DELETED: {$path} ({$scope}, {$scopeId})", $output);
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param int $scopeId
     * @param string|null $value
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    private function setConfigValue(
        string $path,
        string $scopeType,
        int $scopeId,
        ?string $value,
        OutputInterface $output
    ) {
        $this->configWriter->setValue($path, $scopeType, $scopeId, $value);
        $this->printPrimary("{$path} => {$value} ({$scopeType}, {$scopeId})", $output);
    }
}
