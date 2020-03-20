<?php

use App\Core\Application;

defined('ROOT') || define('ROOT', realpath(dirname(__DIR__)));

require_once ROOT . '/src/Core/config.php';
require_once ROOT . '/vendor/autoload.php';

(new Application())->startApp();