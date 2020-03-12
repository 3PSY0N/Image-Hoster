<?php

namespace App\Controllers;

use App\Models\ImgModel;

class Image
{
    public function show(array $slug)
    {
        $imgModel = new ImgModel();
        $dbImage  = $imgModel->getImageBySlugModel(end($slug));

        if ($dbImage) {
            $imgLocation = UPLOAD_FOLDER . $dbImage->img_dir . '/' . $dbImage->img_name;
            if (file_exists($imgLocation)) {
                $this->displayGraphicFile($imgLocation);
            } else {
                $this->notFound();
            }
        } else {
            $this->notFound();
        }
    }

    /**
     * Return the requested graphic file to the browser
     * or a 304 code to use the cached browser copy
     * @param $graphicFileName
     */
    public function displayGraphicFile($graphicFileName)
    {
        $fileModTime = filemtime($graphicFileName);
        // Getting headers sent by the client.
        $headers = $this->getRequestHeaders();
        $etag    = 'W/"' . md5($fileModTime) . '"';
        // get Mime Type
        $size     = getimagesize($graphicFileName);
        $mimeType = $size['mime'];
        // Checking if the client is validating his cache and if it is current.
        if (
            (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $fileModTime)) ||
            (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $etag === trim($_SERVER['HTTP_IF_NONE_MATCH']))
        ) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Cache-Control: max-age=2628000, publics');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($graphicFileName)) . ' GMT', true, 304);
            header("Expires: " . gmdate("D, d M Y H:i:s", strtotime(" 30 day")) . " GMT");
            header("Etag: $etag");
        } else {
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            header('Cache-Control: max-age=2628000, publics');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT', true, 200);
            header('Content-type: ' . $mimeType);
            header('Content-transfer-encoding: binary');
            header('Content-length: ' . filesize($graphicFileName));
            readfile($graphicFileName);
        }
    }

    /**
     * return the browser request header
     * use built in apache ftn when PHP built as module,
     * or query $_SERVER when cgi
     * @return array|false
     */
    private function getRequestHeaders()
    {
        if (function_exists("apache_request_headers")) {
            if ($headers = apache_request_headers()) {
                return $headers;
            }
        }
        $headers = [];

        /** Grab the IF_MODIFIED_SINCE header */
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $headers['If-Modified-Since'] = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
        }

        return $headers;
    }

    /**
     * @return $this
     */
    private function notFound()
    {
        $noImage = ROOT . "/public/assets/img/noimage.jpg";

        $this->displayGraphicFile($noImage);

        return $this;
    }
}
