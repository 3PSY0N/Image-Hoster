<?php

namespace App\Handlers;

use App\Models\ImgModel;
use App\Services\Flash;
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
     * @param string $directoryPath
     * @return bool
     */
    public function setUploadsDirectory(string $directoryPath)
    {
        if (!is_dir($directoryPath)) {
            return mkdir($directoryPath, 755, true);
        }

        return false;
    }

    /**
     * @param $fileSize
     */
    public function checkEmptyFile($fileSize): void
    {
        if ($fileSize === 0) {
            $this->flash->setFlash('info', 'Chose a file to upload.', false, '/');
        }
    }

    /**
     * @param $fileError
     */
    public function checkFileError($fileError): void
    {
        if ($fileError !== 0) {
            $this->flash->setFlash('warning', 'Error during file upload, try again or contact an Administrator.', false, '/');
        }
    }


    /**
     * @param $fileName
     * @param $fileTmpName
     */
    public function checkFileExtMime($fileName, $fileTmpName): void
    {
        if (!$this->isAllowedFileExt($fileName) && !$this->isAllowedFileMime($fileTmpName)) {
            $this->flash->setFlash('warning', 'Incorrect file type, must be: <strong>' . $this->getAlowedTypes() . '</strong>', false, '/');
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
            $this->flash->setFlash('warning', 'File size too big! Max allowed is <b>' . $this->getSizeMax() . '</b>', false, '/');
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
     * @param string $imgDir
     * @param string $fileNameNew
     * @param string $fileNameSlug
     * @param string $deleteToken
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

            $this->flash->setFlash('success', 'Upload success: <b>' . $fileName . '</b>', false);
            $this->flash->setFlash('info', 'Direct access: <b>https://' . $_SERVER['HTTP_HOST'] . SHOW_IMG_ROUTE . $fileNameSlug . '</b>', true, '/');
        } else {
            $this->flash->setFlash('danger', 'Error during file upload, try again.', false, '/');
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
        $pageId  = (int)Toolset::explodeUrlParam($slug[1]);
        $imgSlug = Toolset::explodeUrlParam($slug[2]);

        $imgData   = $this->getImageBySlug($imgSlug);
        $directory = UPLOAD_FOLDER . $imgData->img_dir . '/' . $imgData->img_name;

        if (!file_exists($directory) || !unlink($directory)) {
            $this->flash->setFlash('warning', 'An error occurred while processing your request, please try again or contact an administrator.', false, '/profile');
        }

        if ($imgData && $this->delImageBySlug($imgData->img_slug, base64_decode(Session::get('userSlug')))) {
            $this->flash->setFlash('success', 'Picture deleted !', false);
            Toolset::redirect('/profile?page=' . $pageId);
        } else {
            $this->flash->setFlash('warning', 'An error occurred while processing your request, please try again or contact an administrator.', false, '/profile');
        }
    }

    /**
     * @return bool
     */
    public function purge()
    {
        $imgData = $this->getAllImagesListModel();

        foreach ($imgData as $img) {
            $directory = UPLOAD_FOLDER . $img->img_dir . '/' . $img->img_name;

            if (!file_exists($directory)) {
                $this->purgeImgBySlug($img->img_slug);
            }
        }
        return true;
    }

}