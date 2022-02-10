<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Magento;

use Montikids\MagentoCliUtil\Enum\N98CommandInterface;
use Montikids\MagentoCliUtil\Service\RunN98Command;

/**
 * Reads env.php Magento config file values
 */
class EnvFileReader
{
    /**
     * @var RunN98Command
     */
    private $n98;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->n98 = new RunN98Command();
    }

    /**
     * @param string $path
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function readStringValue(string $path): ?string
    {
        $result = null;
        $value = $this->n98->execute(N98CommandInterface::CONFIG_ENV_SHOW, [$path]);

        if (null !== $value) {
            $result = trim($value);
        }

        return $result;
    }

    /**
     * @param string $path
     * @return int|null
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
}
