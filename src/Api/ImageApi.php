<?php

namespace App\Api;

use App\Handlers\UserHandler;
use App\Models\ImgModel;
use App\Handlers\ImgHandler;
use App\Models\UserModel;

class ImageApi extends ImgHandler
{
    /**
     * @throws \Exception
     */
    public function postImage()
    {
        $hmac  = hash('ripemd160', 'test@test.com');
        $hmac2 = hash('ripemd160', date('r', time()));

        $apiKey = [
            'url' => '69e6130b6badf9fb58f8e835bdafae7c44ca68fa'
        ];

        $tokenKey = '$argon2id$v=19$m=65536,t=4,p=1$V0lxWmNmTmZOTkhKOEJrbQ$DvyGZYBBDDqvg7zkIO6G1YKWLznciGAb5Uej6/I2lNE';

        if (isset($_POST)) {
            $imgModel    = new ImgModel();
            $userHandler = new UserHandler();

            $publicKey  = $_POST['publicKey'];
            $privateKey = $_POST['privateKey'];
            $userData   = $userHandler->getUserByApiKey($publicKey);

            if ($userData && $_POST['publicKey'] === $userData->api_public && password_verify($privateKey, $userData->api_private)) {
                $file            = $_FILES['externalTool'];
                $fileName        = $file['name'];
                $fileTmpName     = $file['tmp_name'];
                $fileSize        = $file['size'];
                $fileError       = $file['error'];
                $fileNameExt     = $this->getFileExt($fileName);
                $fileNameSlug    = $this->tokenizer();
                $deleteToken     = $this->tokenizer(20);
                $fileNameNewExt  = $fileNameSlug . '.' . $fileNameExt;
                $fileDestination = UPLOAD_FOLDER . $fileNameNewExt;
                $userId          = $userData->api_uid;

                if ($fileError === 0) {
                    if ($this->isAllowedFileExt($fileName) && $this->isAllowedFileMime($fileTmpName)) {
                        if ($imgModel->storeImgInDb($userId, $fileNameNewExt, $fileNameSlug, $deleteToken, $fileSize)
                            && move_uploaded_file($fileTmpName, $fileDestination)) {
                            $jsonData['url'] = $fileNameSlug;
                            $jsonData['del'] = $deleteToken;
                            echo json_encode($jsonData);
                        } else {
                            echo "Error during upload";
                            http_response_code(202);
                        }
                    } else {
                        echo "Invalid fily type/mime";
                        http_response_code(503);
                    }
                } else {
                    echo "Error during upload";
                    http_response_code(503);
                }
            } else {
                echo 'Invalid keys/auth';
                http_response_code(401);
            }
        }
    }
}