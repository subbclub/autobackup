<?php

use AutoBackup\Exception\ProviderException;
use AutoBackup\ProviderOptions;
use AutoBackup\Providers\Yandex;

require_once "vendor/autoload.php";

/** Configuration */

$directoryWithFilesMaskToScan = "/home/bitrix/www/bitrix/backup/*.tar.*";
$baseRemoteDirectoryPath = "/backups/";
$token = "";


$FILES_CMD = "ls $directoryWithFilesMaskToScan";
$result = shell_exec($FILES_CMD);
$arrayFiles = explode("\n", $result);

$resultFiles = [];
foreach ($arrayFiles as $index => $arrayFile) {
    if (!empty($arrayFile)) {
        $resultFiles[] = $arrayFile;
    }
}

$options = new ProviderOptions\Yandex();
$options->token = $token;
$options->remoteDirectoryPath = $baseRemoteDirectoryPath;
$options->fileWebPath = "https://your.site.there/backups/there/";

$yandexProvider = new Yandex($options);

try {
    $yandexProvider->proceedBackup($resultFiles);
} catch (ProviderException $e) {
    echo $e->getMessage();
}