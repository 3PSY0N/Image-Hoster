<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Handlers\UserHandler;
use App\Services\Flash;
use App\Services\Logs;
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
        if (Session::isConnected()) { Toolset::redirect('/'); }

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
                $msg->setFlash('warning', 'Identity/Password cannot be empty.', null,false, '/login');
            } else {
                $user = $userHandler->getUserByEmail($inputIdentity);

                if ($user && password_verify($inputPassword, $user->usr_pswd)) {
                    if ($user->usr_reg_date) {
                        Session::setConnected(true);
                        Session::setAdmin($user->usr_admin);
                        Session::set('userSlug', Toolset::b64encode($user->usr_slug));

                        Logs::createLog('Last login for ' . $user->usr_slug, Logs::INFO);
                        $msg->setToast('success', 'Login success !', 'Login', '/user/dashboard');
                    } else {
                        $msg->setFlash('warning', 'Please activate your account before login in.', null,false, '/login');
                    }
                } else {
                    Logs::createLog('Bad login attempt for ' . $inputIdentity, Logs::ERROR);

                    $msg->setFlash('warning', 'Identity/Password does not exist.', null,false, '/login');
                }
            }
        }
    }

    public function logout()
    {
        $msg = new Flash();
        $msg->setToast('info', 'You are now logged out, see you soon !', 'LogOut');
        Session::checkUserIsConnected();
        Session::logout();
    }
}

// TODO : Make Reset password