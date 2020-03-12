<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Services\Session;

class Errors extends Twig
{
    public function e404()
    {
        http_response_code(404);
        echo $this->twig->render('error/404.twig', [
            'isConnected' => Session::isConnected(),
            'profileLink' => Session::get('userName')
        ]);
    }
}