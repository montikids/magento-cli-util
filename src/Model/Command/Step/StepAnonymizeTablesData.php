<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\Config\AnonymizeInterface;
use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Exception\InvalidConfigException;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Db\Connection;
use Montikids\MagentoCliUtil\Model\Magento\EnvFileReader;
use Montikids\MagentoCliUtil\Model\Magento\ValueEncryptor;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Processes Magento tables data anonymization according to the config
 */
class StepAnonymizeTablesData
{
    use OutputFormatTrait;

    /**
     * @var ValueEncryptor
     */
    private $configValueEnc;

    /**
     * @var EnvFileReader
     */
    private $envFileReader;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
        $this->envFileReader = new EnvFileReader();
        $this->configValueEnc = new ValueEncryptor();
    }

    /**
     * @param array<string, mixed> $config
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    public function execute(array $config, OutputInterface $output): void
    {
        $tablesToProcess = $config[AnonymizeInterface::SECTION_TABLES] ?? [];

        if (false === empty($tablesToProcess)) {
            $this->printTitle('Anonymize tables data...', $output);

            $queries = $this->prepareTableQueries($tablesToProcess);
            $connection = Connection::getInstance();

            foreach ($queries as $tableName => $query) {
                $this->printPrimary("- $tableName", $output);

                $connection->query($query);

                if (true === $output->isVerbose()) {
                    $affectedRows = $connection->affectedRows();

                    $this->printSecondary($query, $output);
                    $this->printSeparatorSmall($output);

                    $this->printSecondary("Affected {$affectedRows} rows", $output);
                    $this->printEmptyLine($output);
                }
            }
        } else {
            $this->printSecondary("No tables to anonymize. Please, check the config.", $output);
        }
    }

    /**
     * @param array<string, array<string, array<string|null|bool>|null>|null> $tables
     * @return array<string, string>
     * @throws InvalidConfigException
     */
    private function prepareTableQueries(array $tables): array
    {
        $result = [];
        $tablePrefix = $this->envFileReader->readStringValue(EnvFileInterface::PATH_DB_TABLE_PREFIX);

        foreach ($tables as $tableName => $columns) {
            $skipTable = (null === $columns);

            if (true === $skipTable) {
                continue;
            }

            $fullTableName = "{$tablePrefix}{$tableName}";
            $columnExpressions = $this->prepareColumnExpression($columns);
            $tableUpdateSql = $this->prepareTableUpdateSql($tableName, $columnExpressions);
            $result[$fullTableName] = $tableUpdateSql;
        }

        return $result;
    }

    /**
     * @param array $columns
     * @return array
     * @throws InvalidConfigException
     */
    private function prepareColumnExpression(array $columns): array
    {
        $result = [];

        foreach ($columns as $fieldName => $columnOptions) {
            $skipField = (null === $columnOptions);

            if (true === $skipField) {
                continue;
            }

            $columnExpression = $this->prepareColumnSetSql($fieldName, $columnOptions);
            $result[] = $columnExpression;
        }

        return $result;
    }

    /**
     * @param array<string|null|bool> $options
     * @return string
     * @throws InvalidConfigException
     */
    private function prepareColumnSetSql(string $fieldName, array $options): string
    {
        $this->validateOptionNamesWithException(array_keys($options));

        $value = $options[AnonymizeInterface::FIELD_OPTION_VALUE] ?? '';
        $fieldToConcat = $options[AnonymizeInterface::FILED_OPTION_FIELD_TO_CONCAT] ?? null;
        $postfix = $options[AnonymizeInterface::FIELD_OPTION_POSTFIX] ?? null;
        $isPasswordField = $options[AnonymizeInterface::FIELD_OPTION_IS_PASSWORD] ?? false;
        $concatFieldName = $options[AnonymizeInterface::FIELD_OPTION_CONCAT_FIELD_NAME] ?? false;

        if (null !== $value) {
            $concatParts = [
                "'$value'",
            ];

            if (false === $isPasswordField) {
                if (null !== $fieldToConcat) {
                    $concatParts[] = "`$fieldToConcat`";
                }

                if (null !== $postfix) {
                    $concatParts[] = "'{$postfix}'";
                }

                if (true === $concatFieldName) {
                    $concatParts[] = "'_{$fieldName}'";
                }
            } else {
                $encryptionKey = $this->configValueEnc->getLatestCryptKey();
                $password = $value;
                $salt = $encryptionKey;
                $passwordVersion = 1;
                $passwordHash = "SHA2('{$salt}{$password}', 256)";

                $concatParts = [
                    $passwordHash,
                    "':{$salt}:{$passwordVersion}'",
                ];
            }

            $concatPartsString = implode(', ', $concatParts);
            $result = "`{$fieldName}` = CONCAT({$concatPartsString})";
        } else {
            $result = "`{$fieldName}` = NULL";
        }

        return $result;
    }

    /**
     * @param string $tableName
     * @param array $columnExpressions
     * @return string
     */
    private function prepareTableUpdateSql(string $tableName, array $columnExpressions): string
    {
        $columnExpressionsStr = implode(', ', $columnExpressions);
        $result = "UPDATE `{$tableName}` SET {$columnExpressionsStr}";

        return $result;
    }

    /**
     * @param array $options
     * @return void
     * @throws InvalidConfigException
     */
    private function validateOptionNamesWithException(array $options): void
    {
        $allowedOptions = AnonymizeInterface::FIELD_OPTIONS;
        $incorrectOptions = array_diff($options, $allowedOptions);

        if (count($incorrectOptions) > 0) {
            $incorrectOptionsStr = implode(', ', $incorrectOptions);
            $error = "The following config fields are not supported: {$incorrectOptionsStr}";

            throw new InvalidConfigException($error);
        }
    }
}
