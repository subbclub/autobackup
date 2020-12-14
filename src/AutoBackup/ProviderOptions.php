<?php


namespace AutoBackup;

use AutoBackup\Exception\OptionsException;

abstract class ProviderOptions
{
    private array $options;

    /**
     * @var string
     */
    public string $remoteDirectoryPath;
    /**
     * @var string
     */
    public string $fileWebPath;
    /**
     * @var bool
     */
    public bool $consoleOutput = false;
    /**
     * @var string
     */
    public string $proxyServer;

    /**
     * @var int
     */
    public int $proxyPort;

    /**
     * @var bool
     */
    public bool $useProxy = false;

    /**
     * @var string
     */
    public string $proxyUser;

    /**
     * @var string
     */
    public string $proxyPass;

    /**
     * @var string
     */
    public string $backupVersionDirName;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws OptionsException
     */
    public function __get(string $name)
    {
        if (!isset($this->options[$name])) {
            throw new OptionsException("No option $name provided");
        }
        return $this->options[$name];
    }
}