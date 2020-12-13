<?php


namespace AutoBackup\ProviderOptions;


use AutoBackup\ProviderOptions;

class Yandex extends ProviderOptions
{
    /**
     * @var string
     */
    public string $token;
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

}