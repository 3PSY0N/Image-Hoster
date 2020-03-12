<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Handlers\UserHandler;
use App\Models\UserModel;
use App\Services\Flash;
use App\Services\Session;
use App\Services\Toolset;

class Login extends Twig
{
    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getLogin()
    {
        $flash = new Flash();
        echo $this->twig->render('login.twig', [
            'flashMsg' => $flash->getFlash(),
        ]);
        $flash->clear();
    }

    public function postLogin()
    {
        if (isset($_POST['login'])) {
            $msg         = new Flash();
            $userHandler = new UserHandler();

            $inputIdentity = $_POST['inputIdentity'];
            $inputPassword = $_POST['inputPassword'];

            if (empty($inputIdentity) || empty($inputPassword)) {
                $msg->setFlash('warning', 'Identity/Password cannot be empty.', false, '/login');
            } else {
                $user = $userHandler->getUserByEmail($inputIdentity);

                if ($user && password_verify($inputPassword, $user->usr_pswd)) {
                    Session::setConnected(true);
                    Session::setAdmin($user->usr_admin);
                    Session::set('userSlug', Toolset::b64encode($user->usr_slug));
                    Session::set('userName', $user->usr_name);
                    $msg->setFlash('success', 'Login success !', false, '/profile');
                } else {
                    $msg->setFlash('warning', 'Identity/Password does not exist.', false, '/login');
                }
            }
        }
    }
}