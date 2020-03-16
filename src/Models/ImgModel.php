<?php

namespace App\Models;

use App\Core\Database;

class ImgModel
{
    /**
     * @param string $imgSlug
     * @return mixed
     */
    public function getImageBySlugModel(string $imgSlug)
    {
        return Database::getPDO()->fetch("
            SELECT * FROM imgup_imgdata WHERE img_slug = :img_slug
        ", [
            ':img_slug' => $imgSlug
        ]);
    }

    /**
     * @return mixed
     */
    protected function getAllImagesListModel()
    {
        return Database::getPDO()->fetchAll("SELECT * FROM imgup_imgdata");
    }

    /**
     * @param string $usrSlug
     * @return mixed
     */
    public function getImagesFromUserModel(string $usrSlug)
    {
        return Database::getPDO()->fetchAll("
            SELECT *
            FROM imgup_imgdata AS img
            LEFT JOIN imgup_users AS usr
            ON	img.img_uid = usr.usr_id
            WHERE usr.usr_slug = :usr_slug
        ", [
            ':usr_slug' => $usrSlug
        ]);
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
    protected function storeImgInDbModel(string $imgDir, string $imgName, string $imgSlug, string $imgDeleteToken, int $imgSize, int $uid = null)
    {
        $query = "INSERT INTO imgup_imgdata (img_dir, img_uid, img_name, img_slug, img_deleteToken, img_size, img_date) 
                  VALUES (:img_dir, :img_uid, :img_name, :img_slug, :img_deleteToken, :img_size, :img_date)";

        return Database::getPDO()->insertUpdate($query, [
            ':img_uid'         => $uid,
            ':img_dir'         => $imgDir,
            ':img_name'        => $imgName,
            ':img_slug'        => $imgSlug,
            ':img_deleteToken' => $imgDeleteToken,
            ':img_size'        => $imgSize,
            ':img_date'        => date('Y-m-d H:i:s', time())
        ]);
    }
}