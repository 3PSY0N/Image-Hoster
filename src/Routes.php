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
// Image
Route\get(SHOW_IMG_ROUTE . '[a-zA-Z0-9-_+~$]+(\?.*)?', controller('App\Controllers\Image', 'show'));
Route\get('/di([\?]page=[0-9]+)?/?([\&]img=[a-zA-Z0-9-+_~$]+)', controller('App\Handlers\ImgHandler', 'deleteImg'));
Route\post('/pi/', controller('App\Api\ImageApi', 'postImage'));
// Public
Route\get('/', controller('App\Controllers\Home', 'displayHome'));
Route\post('/', controller('App\Controllers\Home', 'upload'));
Route\get('/gitstatus', controller('App\Controllers\GitStatus', 'showCommits'));
// Public login/register/logout
Route\get('/register', controller('App\Controllers\Register', 'getRegister'));
Route\post('/register', controller('App\Controllers\Register', 'postRegister'));
Route\get('/register?([\?]token=[a-zA-Z0-9]+)?', controller('App\Controllers\Register', 'checkRegistration'));
Route\get('/login', controller('App\Controllers\Login', 'getLogin'));
Route\post('/login', controller('App\Controllers\Login', 'postLogin'));
Route\get('/logout', controller('App\Controllers\Login', 'logout'));
// Private
Route\get('/user/dashboard', controller('App\Controllers\Gallery', 'displayGallery'));
Route\get('/user/gallery?([\?]page=[0-9]+)?', controller('App\Controllers\Gallery', 'displayGallery'));
Route\any('/user/profile', controller('App\Controllers\UserProfile', 'displayUserProfile'));
Route\get('/user/profile/delete', controller('App\Controllers\UserProfile', 'deleteUser'));

// Admin

// Errors
Route\get('.*', function () {
    (new Errors())->e404();
});