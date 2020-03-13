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
    public function getImageBySlugModel(string $imgSlug)
    {
        return parent::getImageBySlugModel($imgSlug);
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
            error_log($this->uploadErrors($fileError));
            $this->flash->setFlash('warning', 'Error during file upload, try again or contact an Administrator.', false, '/');
        }
    }

    /**
     * @param int $code
     * @return string
     */
    public function uploadErrors(int $code)
    {
        switch ($code) {
            case 1:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini => ' . ini_get("upload_max_filesize");
                break;
            case 2:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case 3:
                $message = "The uploaded file was only partially uploaded";
                break;
            case 4:
                $message = "No file was uploaded";
                break;
            case 5:
                $message = "Missing a temporary folder";
                break;
            case 6:
                $message = "Failed to write file to disk";
                break;
            case 7:
                $message = "File upload stopped by extension";
                break;
            default:
                $message = "Unknown upload error";
                break;
        }

        return $message;
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
    public function uploadFile(string $imgDir, string $fileNameNew, string $fileNameSlug, string $deleteToken, $fileSize, $usrUid, $fileTmpName, string $fileDestination, $fileName): void
    {
        if ($this->storeImgInDb($imgDir, $fileNameNew, $fileNameSlug, $deleteToken, $fileSize, $usrUid) && move_uploaded_file($fileTmpName, $fileDestination)) {
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
     * @param string $imgDeleteToken
     * @param int $imgSize
     * @param int|null $uid
     * @return mixed
     */
    public function storeImgInDb(string $imgDir, string $imgName, string $imgSlug, string $imgDeleteToken, int $imgSize, int $uid = null)
    {
        return $this->storeImgInDbModel($imgDir, $imgName, $imgSlug, $imgDeleteToken, $imgSize, $uid);
    }

    /**
     * @param string $link
     */
    public function setImgLink(string $link)
    {
        Session::set('imgLink', $link);
    }
}