<?php
//routes.php

return [
    '/user/get_picture_captcha'    => ['controller' => 'UserController',    'action' => 'get_picture_captcha',   'auth' => false,],
    '/user/picture_captcha_img'    => ['controller' => 'UserController',    'action' => 'picture_captcha_img',   'auth' => false,],
    '/user/get_email_captcha'      => ['controller' => 'UserController',    'action' => 'get_email_captcha',     'auth' => false,],
    '/user/register'               => ['controller' => 'UserController',    'action' => 'register',              'auth' => false,],
    '/user/login_by_pwd'           => ['controller' => 'UserController',    'action' => 'login_by_pwd',          'auth' => false,],
    '/user/login_by_email'         => ['controller' => 'UserController',    'action' => 'login_by_email',        'auth' => false,],
    '/user/verify_token'           => ['controller' => 'UserController',    'action' => 'verify_token',          'auth' => true,],

    '/music/get_music_list'        => ['controller' => 'MusicController',   'action' => 'get_music_list',        'auth' => true,],
    '/music/toggle_favorites_music'=> ['controller' => 'MusicController',   'action' => 'toggle_favorites_music','auth' => true,],
    '/music/search_music'          => ['controller' => 'MusicController',   'action' => 'search_music',          'auth' => true,],


    //需要鉴权的接口
    '/user/change_pwd_by_old_pwd'        => ['controller' => 'UserController',    'action' => 'change_pwd_by_old_pwd',       'auth' => true,],
    '/user/change_pwd_by_email_captcha'  => ['controller' => 'UserController',    'action' => 'change_pwd_by_email_captcha', 'auth' => true,],

    '/test'     => ['controller' => 'TestController',    'action' => 'test',    'auth' => true,],

    '/test1'    => ['controller' => 'TestController',    'action' => 'test1',   'auth' => false,],
];