<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Handlers\MailerHandler;
use App\Handlers\UserHandler;
use App\Services\Flash;
use App\Services\Session;
use App\Services\Toolset;

class Register
{
    /** @var Twig */
    private $twig;
    /** @var Flash */
    private $flash;
    /** @var UserHandler */
    private $userHandler;

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
    public function getRegister()
    {
        if (Session::isConnected()) {
            Toolset::redirect('/');
        }

        echo $this->twig->render('register.twig', [
            'isConnected' => Session::get('isConnected')
        ]);

        $this->flash->clear();
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function postRegister()
    {
        $inputEmail     = trim($_POST['inputEmail']);
        $inputPassword  = $_POST['inputPassword'];
        $inputRPassword = $_POST['inputRPassword'];
        $acceptTos      = isset($_POST['acceptTos']) ? true : false;

        $validateEmail      = $this->userHandler->validateEmail($inputEmail);
        $checkExistEmail    = $this->userHandler->emailExist($inputEmail);
        $validatePswd       = $this->userHandler->validatePassword($inputPassword, $inputRPassword);
        $validatePswdLength = $this->userHandler->validatePasswordLength($inputPassword);
        $validateTos        = $this->userHandler->validateTos($acceptTos);

        if ($validateEmail && $checkExistEmail && $validatePswd && $validatePswdLength && $validatePswdLength && $validateTos) {
            $mailer       = new MailerHandler();
            $newUserSlug  = $this->userHandler->createUserSlug();
            $pswdHash     = $this->userHandler->hashPassword($inputPassword);
            $userRegToken = bin2hex(openssl_random_pseudo_bytes(random_int(32, 48)));

            if ($this->userHandler->registerUser($newUserSlug, $inputEmail, $pswdHash, $userRegToken)) {
                $mailer->registrationMail($inputEmail, $userRegToken);
                $this->flash->setFlash('success', 'Account created', null, false, '/login');
            }
        }

        echo $this->twig->render('register.twig', [
            'flashMsg' => $this->flash->getFlash(),
            'email'    => $inputEmail
        ]);

        $this->flash->clear();
    }

    public function checkRegistration()
    {
        if (!empty($_GET['token'])) {
            $token = $_GET['token'];
            $user  = $this->userHandler->getUserByRegToken($token);

            if ($user && time() < strtotime($user->usr_token_expire)) {

                $this->userHandler->activateAccount($user->usr_email);
                $this->userHandler->purgeExpiredAccounts();

                $this->flash->setFlash('success', 'Account activated ', null, false, '/login');
            } else {
                $this->flash->setFlash('danger', 'Token expired.', null, false, '/login');
            }
        }
    }
}