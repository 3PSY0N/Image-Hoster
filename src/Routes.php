<?php

use App\Controllers\Errors;
use Siler\Route;

function controller(string $className, string $method): \Closure
{
    return function (array $params) use ($className, $method) {
        $obj = new $className;
        $obj->{$method}($params);
    };
}

Route\get('/', controller('App\Controllers\Home', 'displayHome'));
Route\post('/', controller('App\Controllers\Home', 'upload'));

Route\get('/si/([a-zA-Z0-9-+_~$]+)', controller('App\Controllers\Image', 'show'));
Route\post('/pi/', controller('App\Api\ImageApi', 'postImage'));

Route\get('/login', controller('App\Controllers\Login', 'getLogin'));
Route\post('/login', controller('App\Controllers\Login', 'postLogin'));

Route\get('/logout', controller('App\Controllers\UserProfile', 'logout'));

Route\get('/profile?([\?]page=[0-9]+)?', controller('App\Controllers\UserProfile', 'displayUserProfile'));

Route\get('/showprofile/([a-zA-Z0-9]+)', controller('App\Controllers\ShowProfile', 'showUserProfile'));

Route\get('/gitstatus', controller('App\Controllers\GitStatus', 'showCommits'));

Route\get('.*', function () {
    (new Errors())->e404();
});