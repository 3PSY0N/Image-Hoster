<?php

namespace App\Services;

class Toolset
{
    public static function b64encode(string $string)
    {
        return base64_encode($string);
    }

    public static function b64decode(?string $string)
    {
        return base64_decode($string);
    }

    /**
     * @param int $length
     * @param bool $lower
     * @param bool $upper
     * @param bool $numbers
     * @param bool $specials
     * @return string
     * @throws \Exception
     */
    public static function tokenizer(int $length = 10, bool $lower = true, bool $upper = true, bool $numbers = true, bool $specials = true)
    {
        $chars       = 'abcdefghijklmnopqrstuvwxyz';
        $numbersChr  = '0123456789';
        $specialsChr = '-_+$~';
        $keySpace    = null;

        $keySpace .= ($lower) ? $chars : false;
        $keySpace .= ($upper) ? mb_strtoupper($chars) : false;
        $keySpace .= ($numbers) ? $numbersChr : false;
        $keySpace .= ($specials) ? $specialsChr : false;

        if (empty($keySpace)) {
            throw new \Exception('At least one or more params must be TRUE');
        }

        $pieces = [];
        $max    = mb_strlen($keySpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keySpace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    public static function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    public static function explodeUrlParam($param)
    {
        return explode('=',  $param)[1];
    }

    /**
     * @return string
     */
    public static function siteUrl()
    {
    $link = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $link .= "://";
    $link .= $_SERVER['HTTP_HOST'];

    return $link;
    }

    /**
     * time in seconds, if minutes = true, minutes will be converted to seconds
     * @param int $time
     * @param bool $minutes
     * @return false|string
     */
    public static function setExpireDate(int $time = null , bool $minutes = false)
    {
        if ($minutes) {
            $time = ($time * 60);
        }
        return date('Y-m-d H:i:s', time() + $time);
    }

    /**
     * @param string $directoryPath
     * @return bool
     */
    public static function makeDirectoryIfNotExist(string $directoryPath): bool
    {
        if (!is_dir($directoryPath)) {
            return mkdir($directoryPath, 755, true);
        }

        return false;
    }
}