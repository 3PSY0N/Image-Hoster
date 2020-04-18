<?php

namespace App\Handlers;

use App\Models\ImgModel;
use App\Services\Flash;
use App\Services\Logs;
use App\Services\Session;
use App\Services\Toolset;
use Exception;

class ImgHandler extends ImgModel
{
    private $flash;

    public function __construct()
    {
        $this->flash = new Flash();
    }

    public function getImgLink()
    {
        return Session::get('imgLink');
    }

    public function clearImgLink()
    {
        Session::destroy('imgLink');
    }

    public static function getImgDimensions($imgDir, $imgName)
    {
        $path = UPLOAD_FOLDER . $imgDir . '/' . $imgName;
        return getimagesize($path);
    }

    /**
     * @param int $length
     * @param bool $lower
     * @param bool $upper
     * @param bool $numbers
     * @param bool $specials
     * @return string
     * @throws Exception
     */
    public function setNewToken(int $length = 10, bool $lower = true, bool $upper = true, bool $numbers = true, bool $specials = true)
    {
        return Toolset::tokenizer($length, $lower, $upper, $numbers, $specials);
    }

    /**
     * @param string $imgSlug
     * @return mixed
     */
    public function getImageBySlug(string $imgSlug)
    {
        return parent::getImageBySlugModel($imgSlug);
    }

    /**
     * @param string $imgSlug
     * @param string $userSlug
     * @return mixed
     */
    public function delImageBySlug(string $imgSlug, string $userSlug)
    {
        return parent::delImageBySlugModel($imgSlug, $userSlug);
    }

    /**
     * @param $fileSize
     */
    public function checkEmptyFile($fileSize): void
    {
        if ($fileSize === 0) {
            $this->flash->setFlash('info', 'Chose a file to upload.', null, false, '/');
        }
    }

    /**
     * @param $fileError
     */
    public function checkFileError($fileError): void
    {
        if ($fileError !== 0) {
            $this->flash->setFlash('warning', 'Error during file upload, try again or contact an Administrator.', null, false, '/');
        }
    }

    /**
     * @param $fileName
     * @param $fileTmpName
     */
    public function checkFileExtMime($fileName, $fileTmpName): void
    {
        if (!$this->isAllowedFileExt($fileName) && !$this->isAllowedFileMime($fileTmpName)) {
            $this->flash->setFlash('warning', 'Incorrect file type, must be: <strong>' . $this->getAlowedTypes() . '</strong>', null, false, '/');
        }
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function isAllowedFileExt(string $fileName): bool
    {
        $eType = $this->getFileExt($fileName);

        return in_array($eType, ALLOWED_EXT);
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFileExt(string $fileName): string
    {
        $fileExt = explode('.', $fileName);

        return strtolower(end($fileExt));
    }

    /**
     * @param string $fileTmpName
     * @return bool
     */
    public function isAllowedFileMime(string $fileTmpName): bool
    {
        $mType = mime_content_type($fileTmpName);

        return in_array($mType, ALLOWED_MIME);
    }

    /**
     * @return string
     */
    public function getAlowedTypes()
    {
        $allowExt = "";
        foreach (ALLOWED_EXT as $ext) {
            $allowExt .= ' ' . $ext . ' ';
        }

        return $allowExt;
    }

    /**
     * @param $fileSize
     */
    public function checkAllowedFileSize($fileSize): void
    {
        if ($fileSize > SIZE_MAX) {
            $this->flash->setFlash('warning', 'File size too big! Max allowed is <b>' . $this->getSizeMax() . '</b>', null, false, '/');
        }
    }

    /**
     * @return string
     */
    public function getSizeMax()
    {
        return $this->bytePrefix(SIZE_MAX);
    }

    /**
     * @param int $bytes
     * @param string $prefix
     * @param int $decimals
     * @return string
     */
    public function bytePrefix(int $bytes, string $prefix = 'B', int $decimals = 2)
    { // $prefix - o for French -- B for english
        $size   = [' o', ' ki', ' Mi', ' Gi', ' Ti', ' Pi', ' Ei', ' Zi', ' Yi'];
        $factor = intval(floor((strlen($bytes) - 1) / 3));

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor] . $prefix;
    }

    public function correctImageOrientation($filename) {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if($orientation != 1){
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);
                    }
                    imagejpeg($img, $filename, 100);
                }
            }
        }
    }

    /**
     * @param int $chars
     * @return string
     * @throws Exception
     */
    public function checkAndSetNewSlug(int $chars): string
    {
        $newSlug = $this->setNewToken($chars);

        if ($this->getImageBySlugModel($newSlug)) {
            Logs::createLog('Duplicate token entry' . $newSlug, Logs::WARN);
            $newSlug = $this->checkAndSetNewSlug(strlen($newSlug) + 1);
        }

        return $newSlug;
    }

    /**
     * @param string $imgDir
     * @param string $fileNameNew
     * @param string $fileNameSlug
     * @param $fileSize
     * @param $usrUid
     * @param $fileTmpName
     * @param string $fileDestination
     * @param $fileName
     */
    public function uploadFile(string $imgDir, string $fileNameNew, string $fileNameSlug, $fileSize, $usrUid, $fileTmpName, string $fileDestination, $fileName): void
    {
        if ($this->storeImgInDb($imgDir, $fileNameNew, $fileNameSlug, $fileSize, $usrUid) && move_uploaded_file($fileTmpName, $fileDestination)) {
            $this->setImgLink('https://' . $_SERVER['HTTP_HOST'] . SHOW_IMG_ROUTE . $fileNameSlug);

            $this->flash->setFlash('success', 'Upload success: <b>' . $fileName . '</b>', null, false);
            $this->flash->setFlash('info', 'Direct access: <b>https://' . $_SERVER['HTTP_HOST'] . SHOW_IMG_ROUTE . $fileNameSlug . '</b>', null, true,  '/');
        } else {
            $this->flash->setFlash('danger', 'Error during file upload, try again.', null, false, '/');
        }
    }

    /**
     * @param string $imgDir
     * @param string $imgName
     * @param string $imgSlug
     * @param int $imgSize
     * @param int|null $uid
     * @return mixed
     */
    public function storeImgInDb(string $imgDir, string $imgName, string $imgSlug, int $imgSize, int $uid = null)
    {
        return $this->storeImgInDbModel($imgDir, $imgName, $imgSlug, $imgSize, $uid);
    }

    /**
     * @param string $link
     */
    public function setImgLink(string $link)
    {
        Session::set('imgLink', $link);
    }

    /**
     * @param array $slug
     */
    public function deleteImg(array $slug): void
    {
        Session::checkUserIsConnected();
        $pageId  = (int)Toolset::explodeUrlParam($slug[1]);
        $imgSlug = Toolset::explodeUrlParam($slug[2]);
        $imgData   = $this->getImageBySlug($imgSlug);

        if ($imgData && $this->delImageBySlug($imgData->img_slug, base64_decode(Session::get('userSlug')))) {

            $directory = UPLOAD_FOLDER . $imgData->img_dir . '/' . $imgData->img_name;
            if (!file_exists($directory) || !unlink($directory)) {
                $this->flash->setToast('warning', 'An error occurred while processing your request, please try again or contact an administrator.', 'Error', '/user/dashboard');
            }
            Logs::createLog($imgData->img_name . ' was deleted.', Logs::INFO);

            $this->flash->setToast('info','Picture <strong>'. $imgData->img_slug .'</strong> deleted !', 'Status', '/user/gallery?page=' . $pageId);
        } else {
            $this->flash->setToast('warning', 'An error occurred while processing your request, please try again or contact an administrator.', 'Error', '/user/dashboard');
        }
    }

    /**
     * @param string $userSlug
     */
    public function deleteAllImagesFromUser(string $userSlug)
    {
        $images = $this->getImagesFromUserModel($userSlug);

        foreach ($images as $image) {
            $file = UPLOAD_FOLDER . $image->img_dir . '/' . $image->img_name;
            unlink($file);
        }
    }
}