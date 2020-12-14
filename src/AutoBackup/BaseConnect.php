<?php


namespace AutoBackup;


use AutoBackup\Exception\ProviderException;

abstract class BaseConnect
{
    protected string $remoteDirectoryPath;
    protected string $proxyServer;
    protected int $proxyPort;
    protected bool $consoleOutput;
    protected string $fileWebPath;
    protected array $logOutput;
    protected bool $useProxy;
    protected string $proxyUser;
    protected string $proxyPass;
    protected string $backupVersionDirName;

    public function __construct(ProviderOptions $options)
    {

        $this->remoteDirectoryPath = $options->remoteDirectoryPath;
        $this->fileWebPath = $options->fileWebPath;
        $this->consoleOutput = $options->consoleOutput;

        $this->useProxy = $options->useProxy;

        if ($this->useProxy) {
            $this->proxyServer = $options->proxyServer;
            $this->proxyPort = $options->proxyPort;

            if (!empty($options->proxyUser)) {
                $this->proxyUser = $options->proxyUser;
                $this->proxyPass = $options->proxyPass;
            }
        }

        $this->backupVersionDirName = isset($options->backupVersionDirName) ? $options->backupVersionDirName : date('Y-m-d');
    }

    protected function stdOutput($stage, $message)
    {
        switch ($this->consoleOutput) {
            case true:
                echo date("d.m.Y H:i:s") . " " . $stage . ": " . $message . "\n";
                break;
            case false:
                $this->logOutput[] = [
                    "date" => date("c"),
                    "stage" => $stage,
                    "message" => $message
                ];

                break;
        }
    }

    /**
     * @param array $files
     * @throws ProviderException
     */
    abstract function proceedBackup(array $files);

}