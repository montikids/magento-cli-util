<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Db;

use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Exception\DatabaseException;
use Montikids\MagentoCliUtil\Exception\InvalidConfigException;
use Montikids\MagentoCliUtil\Model\Magento\EnvFileReader;

/**
 * Database connection static singleton
 *
 * Is a shitty combination of @link https://codeshack.io/super-fast-php-mysql-database-class/, singleton pattern
 * and strong desire to kill myself
 *
 * @deprecated Must be replaced with a normal library
 */
class Connection
{
    /**
     * @var Connection|null
     */
    private static $instance;

    /**
     * @var EnvFileReader
     */
    private $envFileReader;

    /**
     * @var \mysqli
     */
    private $connection;

    /**
     * @var \mysqli_stmt|false
     */
    private $query;

    /**
     * @var bool
     */
    private $queryClosed;

    /**
     * @throws \Exception
     */
    private function __construct()
    {
        $this->envFileReader = new EnvFileReader();
        $this->establishConnection();
    }

    /**
     * Prevent cloning the object
     *
     * @return void
     */
    private function __clone()
    {
        // phpcs:ignore
    }

    /**
     * @param string $sql
     * @param array $params
     * @return void
     * @throws DatabaseException
     */
    public function query(string $sql, array $params = []): void
    {
        if (false === $this->queryClosed) {
            $this->query->close();
        }

        $this->query = $this->connection->prepare($sql);

        if (false !== $this->query) {
            if (count($params) > 1) {
                $types = '';
                $args_ref = [];

                foreach ($params as $k => &$arg) {
                    if (is_array($params[$k])) {
                        foreach ($params[$k] as $j => &$a) {
                            $types .= $this->_getType($params[$k][$j]);
                            $args_ref[] = &$a;
                        }
                    } else {
                        $types .= $this->getType($params[$k]);
                        $args_ref[] = &$arg;
                    }
                }

                array_unshift($args_ref, $types);
                call_user_func_array([$this->query, 'bind_param'], $args_ref);
            }

            $this->query->execute();

            if (0 !== $this->query->errno) {
                $error = "Unable to process MySQL query (check your params) - {$this->query->error}";
                $this->processCriticalError($error);
            }

            $this->queryClosed = false;
        } else {
            $error = "Unable to prepare MySQL statement (check your syntax) - {$this->connection->error}";
            $this->processCriticalError($error);
        }
    }

    /**
     * @return string
     */
    public function affectedRows(): string
    {
        $result = (string)$this->query->affected_rows;

        return $result;
    }

    /**
     * @return Connection
     * @throws \Exception
     */
    public static function getInstance(): Connection
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public static function close(): void
    {
        if (null === self::$instance) {
            return;
        }

        $connection = self::$instance->connection;

        if ($connection instanceof \mysqli) {
            $connection->close();
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function establishConnection(): void
    {
        $host = $this->envFileReader->readStringValue(EnvFileInterface::DB_CONNECTION_DEFAULT_HOST);
        $user = $this->envFileReader->readStringValue(EnvFileInterface::DB_CONNECTION_DEFAULT_USERNAME);
        $password = $this->envFileReader->readStringValue(EnvFileInterface::DB_CONNECTION_DEFAULT_PASSWORD);
        $dbName = $this->envFileReader->readStringValue(EnvFileInterface::DB_CONNECTION_DEFAULT_DBNAME);

        if ((null === $host) || (null === $user) || (null === $dbName)) {
            $this->processSettingsReadError();
        }

        $this->connection = new \mysqli($host, $user, $password, $dbName);

        if (null !== $this->connection->connect_error) {
            $this->processCriticalError("Failed to connect to MySQL - {$this->connection->connect_error}");
        }

        $this->connection->set_charset('utf8');
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    private function processSettingsReadError(): void
    {
        $error = "Can't read the default DB connection settings. Probably, Magento is not installed.";
        $error .= ' Please, check your config file: ' . EnvFileInterface::FILE_PATH;

        throw new InvalidConfigException($error);
    }

    /**
     * @param string $error
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function processCriticalError(string $error): void
    {
        throw new DatabaseException($error);
    }

    /**
     * @param mixed $var
     * @return string
     * @deprecated Refactor
     */
    private function getType($var): string
    {
        if (is_string($var)) {
            return 's';
        } elseif (is_float($var)) {
            return 'd';
        } elseif (is_int($var)) {
            return 'i';
        }

        return 'b';
    }
}
