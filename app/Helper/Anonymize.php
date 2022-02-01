<?php
declare(strict_types=1);
// @codingStandardsIgnoreFile

namespace App\Helper;

use Symfony\Component\Yaml\Yaml;

class Anonymize
{
    /**
     * @var string
     */
    protected $encryptKey;

    /**
     * @var null|array
     */
    protected $parsedConfig;

    /**
     * @var string
     */
    protected $cryptKey;

    /**
     * Anonymize constructor.
     * @param $cryptKey
     */
    public function __construct(
        string $cryptKey
    ) {
        $this->cryptKey = $cryptKey;
    }

    /**
     * @return mixed
     */
    public function readYamlConfig(): ?array
    {
        if ($this->parsedConfig === null) {
            $config = Yaml::parseFile(__DIR__ . '/../../../../../cli_util/configs/anonymize/base.yml');

            $variables = $config['variables'];

            foreach ($config['config'] as $tableName => &$columns) {
                foreach ($columns as &$column) {
                    foreach ($column as &$option) {
                        if (isset($variables[$option])) {
                            $option = $variables[$option];
                        }
                    }
                }
            }

            $this->parsedConfig = $config;
        }

        return $this->parsedConfig;
    }

    /**
     * @return array
     */
    public function getSqlQueriesFromConfig(): array
    {
        $yamlConfig = $this->readYamlConfig();
        if (isset($yamlConfig['sql_query']) && is_array($yamlConfig['sql_query'])) {
            return $yamlConfig['sql_query'];
        }

        return [];
    }

    /**
     * @return array
     */
    public function getMagerun2Commands(): array
    {
        $yamlConfig = $this->readYamlConfig();
        if (isset($yamlConfig['magerun2_run']) && is_array($yamlConfig['magerun2_run'])) {
            return $yamlConfig['magerun2_run'];
        }

        return [];
    }

    /**
     * @return array
     */
    public function getPreparedSqlQueries(): array
    {
        $yamlConfig = $this->readYamlConfig();
        $result = [];

        foreach ($yamlConfig['config'] as $tableName => $columns) {
            $colsSql = [];
            array_map(function ($colData) use (&$colsSql) {
                $this->prepareColumnQueryParams(
                    $colData,
                    $colName,
                    $concatString,
                    $concatFieldName,
                    $additionalString,
                    $encrypt,
                    $concatFieldNameAfter
                );

                if ($concatFieldNameAfter) {
                    $colsSql[] = sprintf(
                        '%s=CONCAT(%s, %s, %s)',
                        $colName,
                        $concatString,
                        $concatFieldName,
                        $additionalString
                    );
                } else {
                    $colsSql[] = sprintf(
                        '%s=CONCAT(%s, %s, %s)',
                        $colName,
                        $concatFieldName,
                        $concatString,
                        $additionalString
                    );
                }
            }, $columns);

            $result[$tableName] = sprintf('UPDATE %s SET %s;', $tableName, implode(',', $colsSql));
        }

        return $result;
    }

    /**
     * @param $colData
     * @param $colName
     * @param $concatString
     * @param $concatFieldName
     * @param $additionalString
     * @param $encrypt
     * @param $concatFieldNameAfter
     */
    private function prepareColumnQueryParams(
        $colData,
        &$colName,
        &$concatString,
        &$concatFieldName,
        &$additionalString,
        &$encrypt,
        &$concatFieldNameAfter
    ): void {
        [
            $colName,
            $concatString,
            $concatFieldName,
            $additionalString,
            $encrypt,
            $concatFieldNameAfter,
        ] = $colData;

        if ($encrypt) {
            $concatString = 'CONCAT(SHA2(\'' . $this->cryptKey . $concatString . '\', 256), \':' . $this->cryptKey . ':1\')';
        } else {
            $concatString = '\'' . $concatString . '\'';
        }
        $concatFieldName = $concatFieldName ? $concatFieldName : '\'\'';
        $additionalString = '\'' . $additionalString . '\'';
    }
}
