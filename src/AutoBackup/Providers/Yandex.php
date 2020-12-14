<?php

namespace AutoBackup\Providers;

use AutoBackup\BaseConnect;
use AutoBackup\Exception\ProviderException;
use AutoBackup\ProviderOptions;
use Exception;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Mime;
use Httpful\Request;

class Yandex extends BaseConnect
{
    private string $token;

    function __construct(ProviderOptions\Yandex $options)
    {
        $this->token = $options->token;
        parent::__construct($options);
    }


    /**
     * @param Request $request
     */
    private function setCommonRequestParams(Request &$request)
    {
        $token = $this->token;
        $request->mime(Mime::JSON);
        $request->expects(Mime::JSON);
        $request->addHeader("Authorization", "OAuth $token");

        if ($this->useProxy) {
            if (isset($this->proxyUser)) {
                $request->useProxy($this->proxyServer, $this->proxyPort, $this->proxyUser, $this->proxyPass);
            } else {
                $request->useProxy($this->proxyServer, $this->proxyPort);
            }
        }
    }


    /**
     * @param array $files
     * @throws ProviderException
     */
    public function proceedBackup(array $files)
    {
        $baseRemoteDirectoryPath = $this->remoteDirectoryPath;
        $fileWebPath = rtrim($this->fileWebPath, '/');
        $backupVersionDirName = $this->backupVersionDirName;

        if (!empty($files)) {

            $dirName = $baseRemoteDirectoryPath . $backupVersionDirName;

            $createRemoteDirRequest = Request::put("https://cloud-api.yandex.net/v1/disk/resources?path=$dirName");
            $this->setCommonRequestParams($createRemoteDirRequest);

            try {
                $response_dir = $createRemoteDirRequest->send();

                $this->stdOutput("remote dir", "dir $dirName created");
                $this->stdOutput("remote dir", $response_dir->code);
            } catch (ConnectionErrorException $e) {
                $this->stdOutput("remote dir", "failed call to create directory");
                throw new ProviderException("Unable to proceed createRemoteDir request");
            }

            foreach ($files as &$resultFile) {
                $resultFileNameExploded = explode("/", $resultFile);
                $resultFileName = end($resultFileNameExploded);

                $url = "$fileWebPath/$resultFileName";

                $path = "disk:$dirName/$resultFileName";
                $this->stdOutput("file upload", $resultFile . " > " . $path);
                $pathEncoded = urlencode($path);
                $urlEncoded = urlencode($url);

                $checkFileUploadedPreviouslyRequest = Request::get("https://cloud-api.yandex.net/v1/disk/resources?path=$pathEncoded");
                $this->setCommonRequestParams($checkFileUploadedPreviouslyRequest);

                try {
                    $responseCheck = $checkFileUploadedPreviouslyRequest->send();
                    $fileMd5 = md5_file($resultFile, false);
                    if ($responseCheck->code != 404) {
                        if ($fileMd5 == $responseCheck->body->md5) {
                            $this->stdOutput("file upload", "uploaded previously");
                            continue;
                        }
                    }

                } catch (ConnectionErrorException $e) {
                    $files[] = $resultFile;
                    $this->stdOutput("file upload", "file $resultFile will be proceeded again later");
                    $this->stdOutput("file upload", $e->getMessage());
                }


                $remoteUploadRequest = Request::post("https://cloud-api.yandex.net/v1/disk/resources/upload?url=$urlEncoded&path=$pathEncoded");
                $this->setCommonRequestParams($remoteUploadRequest);

                try {
                    $responseStart = $remoteUploadRequest->send();

                    if ((string)$responseStart->code == "202") {
                        if ($responseStart->body) {
                            if ($responseStart->body->href) {

                                $status = "in-progress";
                                while (!in_array($status, ["success", "failed"])) {
                                    try {
                                        $checkCurrentUploadStateRequest = Request::get($responseStart->body->href);
                                        $this->setCommonRequestParams($checkCurrentUploadStateRequest);
                                        $responseStatus = $checkCurrentUploadStateRequest->send();
                                        $status = $responseStatus->body->status;

                                        $this->stdOutput("file upload", $status);

                                        if (in_array($status, ["success"])) {
                                            break;
                                        }
                                        if (in_array($status, ["failed"])) {
                                            $this->stdOutput("file upload", $responseStatus->code);
                                            $files[] = $resultFile;
                                            $this->stdOutput("file upload", "file $resultFile will be proceeded again later");

                                            break;
                                        }
                                    } catch (Exception $e) {
                                        $this->stdOutput("file upload", "file $resultFile check failed");
                                        $this->stdOutput("file upload", $e->getMessage());
                                    }


                                    sleep(3);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    $files[] = $resultFile;
                    $this->stdOutput("file upload", "file $resultFile will be proceeded again later");
                    $this->stdOutput("file upload", $e->getMessage());
                }
            }
        }
    }
}

