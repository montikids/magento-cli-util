<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Magento;

use Montikids\MagentoCliUtil\Enum\Magento\EnvFileInterface;
use Montikids\MagentoCliUtil\Enum\N98CommandInterface;
use Montikids\MagentoCliUtil\Service\RunN98Command;

/**
 * Works with store config values encrypted by Magento
 */
class ValueEncryptor
{
    /**
     * Latest algorithm hash version that Magento uses for encryption
     * @link https://github.com/magento/magento2/blob/2.4-develop/lib/internal/Magento/Framework/Encryption/Encryptor.php
     * @var int
     */
    private const HASH_VERSION = 3;

    /**
     * @var int
     */
    private const NONCE_LENGTH = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES;

    /**
     * @var string[]|null
     */
    private $cachedKeys;

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
     * @param string $value
     * @return string
     * @throws \SodiumException
     */
    public function encrypt(string $value): string
    {
        $cryptKey = $this->getLatestCryptKey();
        $cryptKeyVersion = $this->getCryptKeyCurrentVersion();

        $nonce = random_bytes(self::NONCE_LENGTH);
        $encryptedValue = sodium_crypto_aead_chacha20poly1305_ietf_encrypt($value, $nonce, $nonce, $cryptKey);
        $base64EncodedValue = base64_encode("{$nonce}{$encryptedValue}");

        $result = sprintf(
            '%s:%s:%s',
            $cryptKeyVersion,
            self::HASH_VERSION,
            $base64EncodedValue
        );

        return $result;
    }

    /**
     * Returns latest crypt key version from Magento env file (@return string
     *
     *@see EnvFileInterface::FILE_PATH)
     *
     */
    public function getLatestCryptKey(): string
    {
        $allKeys = $this->getCryptKeys();
        $currentVersion = $this->getCryptKeyCurrentVersion();
        $result = $allKeys[$currentVersion];

        return $result;
    }

    /**
     * @return int
     */
    private function getCryptKeyCurrentVersion(): int
    {
        $allKeys = $this->getCryptKeys();
        $result = count($allKeys) - 1;

        return $result;
    }

    /**
     * Returns all Magento crypt keys (there is more than one, if crypt key was re-generated) stored in the env file
     *
     * @return string[]
     *@see EnvFileInterface::FILE_PATH)
     *
     */
    private function getCryptKeys(): array
    {
        if (null === $this->cachedKeys) {
            $keysStr = $this->n98->execute(N98CommandInterface::CONFIG_ENV_SHOW, [EnvFileInterface::PATH_CRYPT_KEY]);
            $keysStr = trim((string)$keysStr);
            $this->cachedKeys  = preg_split('/\s+/s', $keysStr);
        }

        return $this->cachedKeys;
    }
}
