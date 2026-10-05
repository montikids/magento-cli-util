<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Magento;

use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Exception\InvalidConfigException;

/**
 * Reads env.php Magento config file values
 */
class EnvFileReader
{
    /**
     * Returns a value by its dot-separated path (e.g. 'db.connection.default.host')
     * Returns null if the path doesn't exist or points to a non-scalar value
     *
     * @param string $path
     * @return string|null
     * @throws InvalidConfigException
     */
    public function readStringValue(string $path): ?string
    {
        $value = $this->getEnvConfig();

        foreach (explode('.', $path) as $key) {
            if ((false === is_array($value)) || (false === array_key_exists($key, $value))) {
                $value = null;

                break;
            }

            $value = $value[$key];
        }

        $result = is_scalar($value) ? trim((string)$value) : null;

        return $result;
    }

    /**
     * @param string $path
     * @return int|null
     * @throws InvalidConfigException
     */
    public function readIntValue(string $path): ?int
    {
        $result = null;
        $value = $this->readStringValue($path);

        if (null !== $value) {
            $result = (int)$value;
        }

        return $result;
    }

    /**
     * Reads the file every time because it can be changed during the execution (e.g. by N98 Magerun 2)
     *
     * @return array<string, mixed>
     * @throws InvalidConfigException
     */
    private function getEnvConfig(): array
    {
        $filePath = EnvFileInterface::FILE_PATH;
        $result = is_file($filePath) ? include $filePath : null;

        if (false === is_array($result)) {
            $error = "Unable to read the Magento config file: {$filePath}. Probably, Magento is not installed.";

            throw new InvalidConfigException($error);
        }

        return $result;
    }
}
