<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Handlers\ImgHandler;
use App\Handlers\UserHandler;
use App\Services\Flash;
use App\Services\Logs;
use App\Services\Session;
use App\Services\Toolset;

class UserProfile
{
    private $twig;
    private $userHandler;
    private $flash;

    public function __construct()
    {
        $this->twig        = new Twig();
        $this->flash       = new Flash();
        $this->userHandler = new UserHandler();
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function displayUserProfile()
    {
        Session::checkUserIsConnected();

        $user = $this->userHandler->getUserBySlug(Toolset::b64decode(Session::get('userSlug')));

        if (isset($_POST['editMailBtn'])) {
            $this->editUserEmail();
        }

        if (isset($_POST['btnEditPswd'])) {
            $inputOldPassword  = $_POST['inputOPassword'];
            $inputPassword  = $_POST['inputPassword'];
            $inputRPassword = $_POST['inputRPassword'];

            $validateCurrentPassword = $this->userHandler->passwordVerify($inputOldPassword, $user->usr_pswd);
            $validatePswds           = $this->userHandler->validatePassword($inputPassword, $inputRPassword);
            $validatePswdLength      = $this->userHandler->validatePasswordLength($inputPassword);

            if ($validateCurrentPassword && $validatePswds && $validatePswdLength) {
                $newPasswordHash = $this->userHandler->hashPassword($inputPassword);

                $this->userHandler->setNewPassword($user->usr_email, $newPasswordHash);

                Logs::createLog($user->usr_email . ' changed his/her password.', Logs::INFO);
                $this->flash->setFlash('success', 'Password Changed', 'Profile', true, '/user/profile');
            }
        }

        echo $this->twig->render('admin/user/profile.twig', [
            'flashMsg' => $this->flash->getFlash(),
            'user'     => $user
        ]);

        $this->flash->clear();
    }

    public function editUserEmail()
    {
        Session::checkUserIsConnected();
        $userSlug = Toolset::b64decode(Session::get('userSlug'));
        $user = $this->userHandler->getUserBySlug($userSlug);

        $newEmail = trim($_POST['inputEmail']);

        if ($newEmail !== $user->usr_email) {

            $validateEmail = $this->userHandler->validateEmail($newEmail);
            $checkExistEmail = $this->userHandler->emailExist($newEmail);

            if ($validateEmail && $checkExistEmail) {
                $this->userHandler->setNewEmail($newEmail, $user->usr_email);

                Logs::createLog($user->usr_email . ' changed his/her email for ' . $newEmail, Logs::INFO);
                $this->flash->setFlash('success', 'Email Changed', 'Profile', true, '/user/profile');
            }
        }
    }

    public function deleteUser()
    {
        Session::checkUserIsConnected();
        $imgHandler = new ImgHandler();
        $userSlug   = base64_decode(Session::get('userSlug'));

        $imgHandler->deleteAllImagesFromUser($userSlug);
        $this->userHandler->deleteUser($userSlug);

        Logs::createLog($userSlug . ' deleted his/her account.', Logs::DEL);
        $this->flash->setFlash('info', 'Your account, and pictures has been deleted successfully. See you soon.', 'Account suppression');

        Session::logout();
    }
}