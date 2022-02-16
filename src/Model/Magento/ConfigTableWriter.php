<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Magento;

use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Model\Db\Connection;

/**
 * Has set of methods that simplify working with Magento's 'core_config_data' table
 */
class ConfigTableWriter
{
    /**
     * @var string
     */
    private const TABLE_NAME = 'core_config_data';

    /**
     * Table fields
     */
    private const FIELD_NAME_PATH = 'path';
    private const FIELD_NAME_SCOPE = 'scope';
    private const FIELD_NAME_SCOPE_ID = 'scope_id';
    private const FIELD_NAME_VALUE = 'value';

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
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param int $scopeId
     * @return void
     * @throws \Exception
     */
    public function deleteValue(string $path, string $scopeType, int $scopeId): void
    {
        $connection = Connection::getInstance();

        $query = sprintf(
            'DELETE FROM %s WHERE (%s = ?) AND (%s = ?) AND (%s = ?)',
            $this->getTableName(),
            self::FIELD_NAME_PATH,
            self::FIELD_NAME_SCOPE,
            self::FIELD_NAME_SCOPE_ID
        );

        $params = [trim($path), $scopeType, $scopeId];

        $connection->query($query, $params);
    }

    /**
     * Sets a new value or updates an existed one
     *
     * @param string $path
     * @param string $scopeType
     * @param int $scopeId
     * @param string|null $value
     * @return void
     * @throws \Exception
     */
    public function setValue(string $path, string $scopeType, int $scopeId, ?string $value): void
    {
        $connection = Connection::getInstance();

        $query = sprintf(
            'INSERT INTO %s (%s, %s, %s, %s) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE %s=?',
            $this->getTableName(),
            self::FIELD_NAME_PATH,
            self::FIELD_NAME_SCOPE,
            self::FIELD_NAME_SCOPE_ID,
            self::FIELD_NAME_VALUE,
            self::FIELD_NAME_VALUE
        );

        $params = [trim($path), $scopeType, $scopeId, $value, $value];

        $connection->query($query, $params);
    }

    /**
     * @return string
     */
    private function getTableName(): string
    {
        $tablePrefix = $this->envFileReader->readStringValue(EnvFileInterface::PATH_DB_TABLE_PREFIX);
        $result = $tablePrefix . self::TABLE_NAME;

        return $result;
    }
}
