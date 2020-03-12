<?php

namespace App\Core;

use App\Services\Session;

class Application
{
    public function __construct()
    {
        Database::getPDO()->setup(json_decode(file_get_contents(ROOT . '/src/Core/setupdb.json'), true));
    }

    public function startApp(): void
    {
        Session::start();
        include_once ROOT . '/src/Routes.php';
    }
}