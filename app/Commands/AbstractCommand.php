<?php
declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace App\Commands;

use App\Helper\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 */
abstract class AbstractCommand extends Command
{
    public const CIPHER_AEAD_CHACHA20POLY1305 = 3;

    public const CLI_UTIL_ENV_PATH = 'cli_util.environment';

    protected $rootPath = __DIR__ . '/../../../../../cli_util/';

    protected $allowedEnv = ['local', 'dev', 'stage'];

    protected $dbInstance;

    /***
     * Run any magerun2 command
     *
     * @param $command
     * @return false|string|null
     */
    protected function execMageRun(string $command)
    {
        if ($command) {
            return shell_exec($this->rootPath . 'bin/n98-magerun2.phar ' . $command);
        }

        return false;
    }

    /**
     * Retrieve env name from app/etc/env.php
     *
     * @param OutputInterface $output
     * @return string
     * @throws \Exception
     */
    protected function getEnvironmentType(OutputInterface $output): string
    {
        $envName = $this->readEnvFile(self::CLI_UTIL_ENV_PATH, $output);

        if (!$envName) {
            throw new \Exception('First execute the command "php bin/console configure:init [env_name]"');
        }

        if (!in_array($envName, $this->allowedEnv)) {
            $message = 'Your configuration contains an unauthorized environment. ';
            $message .= 'Please check app/etc/env.php (' . self::CLI_UTIL_ENV_PATH . '). ';
            $message .= 'You can use value: ' . implode(', ', $this->allowedEnv);
            throw new \Exception($message);
        }

        return $envName;
    }

    /**
     * Encrypt value  like as magento
     *
     * @param string $data
     * @return string
     * @throws \SodiumException
     */
    public function encrypt(string $data): string
    {
        $cryptKey = trim($this->execMageRun('config:env:show crypt.key'));
        $keys = preg_split('/\s+/s', trim((string)$cryptKey));
        $keyVersion = count($keys) - 1;
        $key = $keys[$keyVersion];

        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);
        $cipherText = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
            (string)$data,
            $nonce,
            $nonce,
            $key
        );

        $secretStr = $nonce . $cipherText;

        return $keyVersion .
            ':' . self::CIPHER_AEAD_CHACHA20POLY1305 .
            ':' . base64_encode($secretStr);

    }

    /**
     * Read value from app/etc/env.php
     *
     * @param string $arrPath
     * @param OutputInterface $output
     * @return string
     */
    protected function readEnvFile(string $arrPath, OutputInterface $output): string
    {
        $value = (string)$this->execMageRun('config:env:show ' . $arrPath);
        $output->writeln("<comment>Read env variable: {$arrPath} </comment>");
        return trim($value);
    }

    /**
     * @param OutputInterface $output
     * @return Db
     */
    protected function getDbInstance(OutputInterface $output): Db
    {
        if (null === $this->dbInstance) {
            $host = $this->readEnvFile('db.connection.default.host', $output);
            $user = $this->readEnvFile('db.connection.default.username', $output);
            $password = $this->readEnvFile('db.connection.default.password', $output);
            $dbName = $this->readEnvFile('db.connection.default.dbname', $output);
            if (!$host || !$user || !$dbName) {
                throw new \Exception('wrong mysql connection params from app/etc/env.php');
            }

            $output->writeln('<info>Create instance mysql connect</info>');

            $this->dbInstance = new Db($host, $user, $password, $dbName);
        }

        return $this->dbInstance;
    }
}
