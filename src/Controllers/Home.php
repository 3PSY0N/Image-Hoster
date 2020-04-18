<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Handlers\UserHandler;
use App\Services\Flash;
use App\Handlers\ImgHandler;
use App\Services\Logs;
use App\Services\Session;
use App\Services\Toolset;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Home
{
    /** @var Flash */
    private $flash;
    /** @var ImgHandler */
    private $imgHandler;
    /** @var UserHandler */
    private $userHandler;
    private $twig;

    public function __construct()
    {
        $this->twig        = new Twig();
        $this->flash       = new Flash();
        $this->imgHandler  = new ImgHandler();
        $this->userHandler = new UserHandler();
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function displayHome()
    {
        echo $this->twig->render('home.twig', [
            'flashMsg'     => $this->flash->getFlash(),
            'allowedSize'  => $this->imgHandler->getSizeMax(),
            'allowedTypes' => $this->imgHandler->getAlowedTypes(),
            'getImgLink'   => $this->imgHandler->getImgLink(),
            'profileLink'  => Session::get('userName'),
            'isConnected'  => Session::get('isConnected')
        ]);

        $this->flash->clear();
        $this->imgHandler->clearImgLink();
    }

    /**
     * @throws Exception
     */
    public function upload()
    {
        if (isset($_POST['submit'])) {
            $file        = $_FILES['inputFile'];
            $fileName    = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize    = $file['size'];
            $fileError   = $file['error'];
            $user        = $this->userHandler->getUserBySlug(Toolset::b64decode(Session::get('userSlug')));
            $usrUid      = $user ? $user->usr_id : null;
            $fileExt     = $this->imgHandler->getFileExt($fileName);

            if ($fileSize !== 0) {
                $fileNameSlug    = $this->imgHandler->checkAndSetNewSlug(TOKEN_SIZE);
                $fileNameNew     = $fileNameSlug . time() . '.' . $fileExt;
                $directoryName   = date("Y") . '-' . date("m");
                $directoryPath   = UPLOAD_FOLDER . $directoryName;
                $fileDestination = $directoryPath . '/' . $fileNameNew;
            }

            $this->imgHandler->checkEmptyFile($fileSize);
            $this->imgHandler->checkFileError($fileError);
            $this->imgHandler->checkFileExtMime($fileName, $fileTmpName);
            $this->imgHandler->checkAllowedFileSize($fileSize);
            Toolset::makeDirectoryIfNotExist($directoryPath);

            if (mime_content_type($fileTmpName) === "image/jpeg") {
                $this->imgHandler->correctImageOrientation($fileTmpName);
            }
            Logs::createLog($fileNameNew . ' was uploaded.', Logs::INFO);

            $this->imgHandler->uploadFile($directoryName, $fileNameNew, $fileNameSlug, $fileSize, $usrUid, $fileTmpName, $fileDestination, $fileName);
        }
    }
}