<?php
declare(strict_types=1);

namespace Montikids\MagentoCliUtil\Model\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Simplifies printing formatted messages into console output
 */
trait OutputFormatTrait
{
    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printTitle(string $message, OutputInterface $output): void
    {
        $this->printSeparatorBig($output);
        $output->writeln("<question>{$message}</question>");
        $this->printEmptyLine($output);
    }

    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printPrimary(string $message, OutputInterface $output): void
    {
        $output->writeln("<info>{$message}</info>");
    }

    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printSecondary(string $message, OutputInterface $output): void
    {
        $output->writeln("<comment>{$message}</comment>");
    }

    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printEmptyLine(OutputInterface $output): void
    {
        $output->writeln('');
    }
    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printSeparatorSmall(OutputInterface $output): void
    {
        $output->writeln('...');
    }

    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printSeparatorBig(OutputInterface $output): void
    {
        $output->writeln('=====================================');
    }

    /**
     * @param string $message
     * @param OutputInterface $output
     * @return void
     */
    private function printError(string $message, OutputInterface $output): void
    {
        $output->writeln("<error>{$message}</error>");
    }

    /**
     * @param \Throwable $exception
     * @param OutputInterface $output
     * @return void
     */
    private function printException(\Throwable $exception, OutputInterface $output): void
    {
        if (true === $output->isVerbose()) {
            $error = "{$exception->getMessage()}. File: {$exception->getFile()}. Line: {$exception->getLine()}.";
            $error .= "Trace: {$exception->getTraceAsString()}";
        } else {
            $error = $exception->getMessage();
        }

        $this->printError($error, $output);
    }
}
