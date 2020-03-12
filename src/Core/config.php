<?php

define("UPLOAD_FOLDER", ROOT . '/uploads/');
define('SIZE_MB', 10);// Size in MB
define('SIZE_MAX', ((SIZE_MB * 1024) * 1024));// Size in MB
define('ALLOWED_EXT', ["jpg", "jpeg", "png", "gif"]);
define('ALLOWED_MIME', ["image/jpeg", "image/png", "image/gif"]);
define("SHOW_IMG_ROUTE", '/si/');