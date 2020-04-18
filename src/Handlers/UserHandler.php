<?php

namespace App\Handlers;

use App\Models\UserModel;
use App\Services\Flash;

class UserHandler extends UserModel
{
    private $flash;

    public function __construct()
    {
        $this->flash = new Flash();
    }

    /**
     * @return string|string[]
     */
    public function createUserSlug()
    {
        return str_replace('.', '', uniqid('user', true));
    }

    /**
     * @param string $inputEmail
     * @return bool
     */
    public function validateEmail(string $inputEmail)
    {
        if (!filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) {
            $this->flash->setFlash('warning', 'A valid email address is required.');

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $inputEmail
     * @return bool
     */
    public function emailExist(string $inputEmail)
    {
        if ($this->getUserByEmail($inputEmail)) {
            $this->flash->setFlash('warning', 'Email already taken.');

            return false;
        } else {
            return true;
        }
    }

    public function getUserByRegToken(string $token)
    {
        return $this->getUserByRegTokenModel($token);
    }

    /**
     * @param string $inputPassword
     * @param string $inputRPassword
     * @return bool
     */
    public function validatePassword(string $inputPassword, string $inputRPassword): bool
    {
        if (empty($inputPassword) || empty($inputRPassword) || $inputPassword !== $inputRPassword) {
            $this->flash->setFlash('warning', 'New passwords do not match.');

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $inputPassword
     * @param string $currentPassword
     * @return bool
     */
    public function passwordVerify(string $inputPassword, string $currentPassword): bool
    {
        if (!password_verify($inputPassword, $currentPassword)) {

            $this->flash->setFlash('warning', 'Password do not match with the current or is empty.');

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $inputPassword
     * @return bool
     */
    public function validatePasswordLength($inputPassword)
    {
        if (mb_strlen($inputPassword) < 8) {
            $this->flash->setFlash('warning', '
            Password must contain at least 8 chars.<br>
            <small>If possible use special chars, numbers, upper and lower case.</small><br>
            <small>To limit security risks, use a password not used anywhere else.</small>', null, true);

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param bool $acceptTos
     * @return bool
     */
    public function validateTos(bool $acceptTos)
    {
        if (!$acceptTos) {
            $this->flash->setFlash('info', 'To continue, you must read and agree to the ToS and Privacy Policy content.', null, true);

            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $password
     * @return false|string|null
     */
    public function hashPassword(string $password)
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function getUserBySlug(string $slug)
    {
        return $this->getUserBySlugModel($slug);
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function getUserByEmail(string $email)
    {
        return $this->getUserByEmailModel($email);
    }

    /**
     * @param string $publicKey
     * @return mixed
     */
    public function getUserByApiKey(string $publicKey)
    {
        return $this->getUserByApiKeyModel($publicKey);
    }

    /**
     * @param string $userSlug
     * @param string $email
     * @param string $password
     * @param string $userToken
     * @return bool
     */
    public function registerUser(string $userSlug, string $email, string $password, string $userToken)
    {
        return $this->registerNewUserModel($userSlug, $email, $password, $userToken);
    }

    public function setNewEmail(string $newEmail, string $oldEmail)
    {
        return $this->setNewEmailModel($newEmail, $oldEmail);
    }

    public function setNewPassword(string $email, string $pswdHash)
    {
        return $this->setNewPasswordModel($email, $pswdHash);
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function activateAccount(string $email)
    {
        return $this->activateAccountModel($email);
    }

    public function purgeExpiredAccounts()
    {
        return $this->purgeExpiredAccountsModel();
    }

    public function deleteUser(string $userSlug)
    {
        return $this->deleteUserModel($userSlug);
    }
}
