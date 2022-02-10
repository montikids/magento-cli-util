<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command\Step;

use Montikids\MagentoCliUtil\Enum\Config\AnonymizeInterface;
use Montikids\MagentoCliUtil\Exception\InvalidConfigException;
use Montikids\MagentoCliUtil\Model\Command\OutputFormatTrait;
use Montikids\MagentoCliUtil\Model\Db\Connection;
use Montikids\MagentoCliUtil\Model\Magento\ValueEncryptor;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Processes Magento tables data anonymization according to the config
 */
class StepAnonymizeTablesData
{
    use OutputFormatTrait;

    private const OPT_IDX_FIELD_NAME = 0;
    private const OPT_IDX_VALUE = 1;
    private const OPT_IDX_FIELD_TO_CONCAT = 2;
    private const OPT_IDX_POSTFIX = 3;
    private const OPT_IDX_IS_PASSWORD = 4;
    private const OPT_IDX_CONCAT_FIELD_NAME = 5;

    /**
     * @var ValueEncryptor
     */
    private $configValueEnc;

    /**
     * Configure dependencies
     */
    public function __construct()
    {
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
        $tablesToProcess = $config[AnonymizeInterface::SECTION_CONFIG] ?? [];

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
     * @param array<string, array<string, array<string|null|bool>>> $tables
     * @return array<string, string>
     * @throws InvalidConfigException
     */
    private function prepareTableQueries(array $tables): array
    {
        $result = [];

        foreach ($tables as $tableName => $columns) {
            $columnExpressions = [];

            foreach ($columns as $columnOptions) {
                $columnExpression = $this->prepareColumnSetSql($columnOptions);
                $columnExpressions[] = $columnExpression;
            }

            $tableUpdateSql = $this->prepareTableUpdateSql($tableName, $columnExpressions);
            $result[$tableName] = $tableUpdateSql;
        }

        return $result;
    }

    /**
     * @param array<string|null|bool> $options
     * @return string
     * @throws InvalidConfigException
     */
    private function prepareColumnSetSql(array $options): string
    {
        $fieldName = $options[self::OPT_IDX_FIELD_NAME] ?? null;
        $value = $options[self::OPT_IDX_VALUE] ?? null;
        $fieldToConcat = $options[self::OPT_IDX_FIELD_TO_CONCAT] ?? null;
        $postfix = $options[self::OPT_IDX_POSTFIX] ?? null;
        $isPasswordField = $options[self::OPT_IDX_IS_PASSWORD] ?? false;
        $concatFieldName = $options[self::OPT_IDX_CONCAT_FIELD_NAME] ?? false;

        if (null === $fieldName) {
            throw new InvalidConfigException('Field name to anonymize must be specified explicitly');
        }

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
}
