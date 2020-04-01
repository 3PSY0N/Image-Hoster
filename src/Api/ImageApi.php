<?php

namespace App\Api;

use App\Handlers\UserHandler;
use App\Handlers\ImgHandler;

class ImageApi
{
    private $imgHandler;
    private $userHandler;

    public function __construct()
    {
        $this->imgHandler  = new ImgHandler();
        $this->userHandler = new UserHandler();
    }

    /**
     * @throws \Exception
     */
    public function postImage()
    {
        if (isset($_POST['upload']) && $_POST['upload'] === 'api') {
            $userHandler = new UserHandler();

            $publicKey  = $_POST['publicKey'];
            $privateKey = $_POST['privateKey'];
            $userData   = $userHandler->getUserByApiKey($publicKey);

            if ($userData && $_POST['publicKey'] === $userData->api_public && password_verify($privateKey, $userData->api_private)) {
                $userId          = $userData->usr_id;
                $file            = $_FILES['externalTool'];
                $fileName        = $file['name'];
                $fileTmpName     = $file['tmp_name'];
                $fileSize        = $file['size'];
                $fileError       = $file['error'];
                $fileExt     = $this->imgHandler->getFileExt($fileName);

                if ($fileSize !== 0) {
                    $fileNameSlug    = $this->imgHandler->checkAndSetNewSlug(TOKEN_SIZE);
                    $fileNameNew     = $fileNameSlug . time() . '.' . $fileExt;
                    $directoryName   = date("Y") . '-' . date("m");
                    $directoryPath   = UPLOAD_FOLDER . $directoryName;
                    $fileDestination = $directoryPath . '/' . $fileNameNew;
                }

                $this->imgHandler->setUploadsDirectory($directoryPath);

                $error = false;

                if ($fileSize === 0) {
                    http_response_code(403);
                    $error = true;
                }
                if ($fileError !== 0) {
                    http_response_code(500);
                    $error = true;
                }
                if (!$this->imgHandler->isAllowedFileExt($fileName) || !$this->imgHandler->isAllowedFileMime($fileTmpName)) {
                    http_response_code(415);
                    $error = true;
                }
                if ($this->imgHandler->getImageBySlugModel($fileNameSlug)) {
                    $fileNameSlug = $this->imgHandler->setNewToken(strlen($fileNameSlug) + 2);
                    $fileNameNew  = $fileNameSlug . time() . '.' . $fileExt;
                }
                if (!move_uploaded_file($fileTmpName, $fileDestination)) {
                    http_response_code(500);
                    $error = true;
                }

                if (!$error) {
                    $this->imgHandler->storeImgInDb($directoryName, $fileNameNew, $fileNameSlug, $fileSize, $userId);
                    $jsonData['url'] = $fileNameSlug;
                    echo json_encode($jsonData);
                }

            } else {
                echo 'Invalid keys/auth';
                http_response_code(401);
            }
        }
    }
}