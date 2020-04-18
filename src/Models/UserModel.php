<?php

namespace App\Models;

use App\Core\Database;
use App\Services\Toolset;

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

    protected function getUserByRegTokenModel(string $token)
    {
        return Database::getPDO()->fetch("SELECT usr_email, usr_token_expire FROM imgup_users WHERE usr_token = :usr_token", [
            ':usr_token' => $token
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

    /**
     * @param string $userSlug
     * @param string $email
     * @param string $password
     * @param string $userToken
     * @return mixed
     */
    protected function registerNewUserModel(string $userSlug, string $email, string $password, string $userToken)
    {
        $query = "INSERT INTO imgup_users (usr_slug, usr_email, usr_pswd, usr_admin, usr_reg_date, usr_token, usr_token_expire)
                  VALUES (:usr_slug, :usr_email, :usr_pswd, :usr_admin, :usr_reg_date, :usr_token, :usr_token_expire)";

        return Database::getPDO()->IUD($query, [
            ':usr_slug'         => $userSlug,
            ':usr_email'        => $email,
            ':usr_pswd'         => $password,
            ':usr_admin'        => 0,
            ':usr_reg_date'     => null,
            ':usr_token'        => $userToken,
            ':usr_token_expire' => Toolset::setExpireDate(15, true)
        ]);
    }

    protected function setNewEmailModel(string $newEmail, string $oldEmail)
    {
        $query = "UPDATE imgup_users SET usr_email = :usr_newEmail WHERE usr_email = :usr_oldEmail";

        return Database::getPDO()->IUD($query, [
            ':usr_newEmail'        => $newEmail,
            ':usr_oldEmail'        => $oldEmail,
        ]);
    }

    protected function setNewPasswordModel(string $email, string $pswdHash)
    {
        $query = "UPDATE imgup_users SET usr_pswd = :usr_pswd WHERE usr_email = :usr_email";

        return Database::getPDO()->IUD($query, [
            ':usr_pswd'  => $pswdHash,
            ':usr_email' => $email,
        ]);
    }

    protected function activateAccountModel(string $email)
    {
        $query = "UPDATE imgup_users SET usr_reg_date = :usr_reg_date, usr_token = :usr_token, usr_token_expire = :usr_token_expire WHERE usr_email = :usr_email";

        return Database::getPDO()->IUD($query, [
            ':usr_email'        => $email,
            ':usr_reg_date'     => date('Y-m-d H:i:s', time()),
            ':usr_token'        => null,
            ':usr_token_expire' => null
        ]);
    }

    public function purgeExpiredAccountsModel()
    {
        return Database::getPDO()->IUD('DELETE FROM imgup_users WHERE usr_token_expire < :now', [
            ':now' => date('Y-m-d H:i:s', time())
        ]);
    }

    public function deleteUserModel(string $userSlug)
    {
        return Database::getPDO()->IUD('DELETE FROM imgup_users WHERE usr_slug = :usr_slug', [
            ':usr_slug' => $userSlug
        ]);
    }
}