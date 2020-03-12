<?php

namespace App\Controllers;

use App\Handlers\UserHandler;
use App\Services\Session;

class ShowProfile
{
    public function showUserProfile($user = false)
    {
        Session::checkUserIsConnected();
        Session::checkUserIsAdmin();

        $userHandler = new UserHandler();

        if ($user && $userHandler->getUserBySlug($user)) {
            echo "Showing $user";
        } else {
            echo "User does not exist";
        }

    }
}