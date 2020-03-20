<?php

namespace App\Models;

use App\Core\Database;

class UserModel
{
    /**
     * @param $userSlug
     * @return mixed
     */
    protected function getUserBySlugModel(string $userSlug)
    {
        return Database::getPDO()->fetch("SELECT * FROM imgup_users WHERE usr_slug = :usr_slug", [
            ':usr_slug' => $userSlug
        ]);
    }

    /**
     * @param $userEmail
     * @return mixed
     */
    protected function getUserByEmailModel(string $userEmail)
    {
        return Database::getPDO()->fetch("SELECT * FROM imgup_users WHERE usr_email = :usr_email", [
            ':usr_email' => $userEmail
        ]);
    }

    /**
     * @param $publicKey
     * @return mixed
     */
    protected function getUserByApiKeyModel(string $publicKey)
    {
        return Database::getPDO()->fetch("
            SELECT *
            FROM imgup_users AS usr
            LEFT JOIN imgup_api AS api
            ON usr.usr_id = api.api_uid
            WHERE api.api_public = :publicKey
        ", [
            'publicKey' => $publicKey
        ]);
    }
}