<?php

define("UPLOAD_FOLDER", ROOT . '/uploads/');
define('SIZE_MB', 10);// Size in MB
define('SIZE_MAX', ((SIZE_MB * 1024) * 1024));// Size in MB
define('ALLOWED_EXT', ["jpg", "jpeg", "png", "gif"]);
define('ALLOWED_MIME', ["image/jpeg", "image/png", "image/gif"]);
define("SHOW_IMG_ROUTE", '/si/');
// GITLAB
define("GITLAB_API_TOKEN", "gitab api token");
define("GITLAB_PROJECT_ID", gitlab project id);
// SMTPS CONFIGURATION
define("MAIL_HOST", 'your smtp host');
define("MAIL_SMTP_PORT", 465);
define("MAIL_SMTP_ACCOUNT", 'your email');
define("MAIL_SMTP_PASSWORD", 'password');
define("MAIL_NOREPLY", 'no reply adress');
define("MAIL_NOREPLY_NAME", 'no reply name');