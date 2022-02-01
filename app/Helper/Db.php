<?php
declare(strict_types=1);

// @codingStandardsIgnoreFile
/**
 * details - https://codeshack.io/super-fast-php-mysql-database-class/
 */

namespace App\Helper;

class Db
{
    /**
     * @var \mysqli
     */
    protected $connection;

    /**
     * @var
     */
    protected $query;

    /**
     * @var bool
     */
    protected $showErrors = true;

    /**
     * @var bool
     */
    protected $queryClosed = true;

    /**
     * @var int
     */
    public $queryCount = 0;

    /**
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpass
     * @param string $dbname
     * @param string $charset
     */
    public function __construct($dbhost = 'localhost', $dbuser = 'root', $dbpass = '', $dbname = '', $charset = 'utf8')
    {
        $this->connection = new \mysqli(trim($dbhost), trim($dbuser), trim($dbpass), trim($dbname));
        if ($this->connection->connect_error) {
            $this->error('Failed to connect to MySQL - ' . $this->connection->connect_error);
        }
        $this->connection->set_charset(trim($charset));
    }

    /**
     * @param $query
     * @return $this
     */
    public function query($query): Db
    {
        if (!$this->queryClosed) {
            $this->query->close();
        }
        if ($this->query = $this->connection->prepare($query)) {
            if (func_num_args() > 1) {
                $x = func_get_args();
                $args = array_slice($x, 1);
                $types = '';
                $args_ref = [];
                foreach ($args as $k => &$arg) {
                    if (is_array($args[$k])) {
                        foreach ($args[$k] as $j => &$a) {
                            $types .= $this->_getType($args[$k][$j]);
                            $args_ref[] = &$a;
                        }
                    } else {
                        $types .= $this->_getType($args[$k]);
                        $args_ref[] = &$arg;
                    }
                }
                array_unshift($args_ref, $types);
                call_user_func_array([$this->query, 'bind_param'], $args_ref);
            }
            $this->query->execute();
            if ($this->query->errno) {
                $this->error('Unable to process MySQL query (check your params) - ' . $this->query->error);
            }
            $this->queryClosed = false;
            $this->queryCount++;
        } else {
            $this->error('Unable to prepare MySQL statement (check your syntax) - ' . $this->connection->error);
        }
        return $this;
    }

    /**
     * @param null $callback
     * @return array
     */
    public function fetchAll($callback = null): array
    {
        $params = [];
        $row = [];
        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array($this->query, 'bind_result'), $params);
        $result = [];
        while ($this->query->fetch()) {
            $r = [];
            foreach ($row as $key => $val) {
                $r[$key] = $val;
            }
            if ($callback != null && is_callable($callback)) {
                $value = call_user_func($callback, $r);
                if ($value == 'break') {
                    break;
                }
            } else {
                $result[] = $r;
            }
        }
        $this->query->close();
        $this->queryClosed = true;
        return $result;
    }

    /**
     * @return array
     */
    public function fetchArray(): array
    {
        $params = [];
        $row = [];
        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array([$this->query, 'bind_result'], $params);
        $result = [];
        while ($this->query->fetch()) {
            foreach ($row as $key => $val) {
                $result[$key] = $val;
            }
        }
        $this->query->close();
        $this->queryClosed = true;
        return $result;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->connection->close();
    }

    /**
     * @return mixed
     */
    public function numRows()
    {
        $this->query->store_result();
        return $this->query->num_rows;
    }

    /**
     * @return mixed
     */
    public function affectedRows()
    {
        return $this->query->affected_rows;
    }

    /**
     * @return mixed
     */
    public function lastInsertID()
    {
        return $this->connection->insert_id;
    }

    /**
     * @param $error
     */
    public function error($error): void
    {
        if ($this->showErrors) {
            exit($error);
        }
    }

    /**
     * @param $var
     * @return string
     */
    private function _getType($var): string
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
