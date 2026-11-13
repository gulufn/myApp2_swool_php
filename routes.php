<?php
//routes.php
// return [
//     '/test'                       => ['file' => 'controllers/test/test.php',                'auth' => true],
//     '/test1'                      => ['file' => 'controllers/test/test1.php',               'auth' => false],

// ];



return [
    '/user/get_picture_captcha'    => ['controller' => 'UserController',    'action' => 'get_picture_captcha',   'auth' => false,],
    '/user/picture_captcha_img'    => ['controller' => 'UserController',    'action' => 'picture_captcha_img',   'auth' => false,],
    '/user/get_email_captcha'      => ['controller' => 'UserController',    'action' => 'get_email_captcha',     'auth' => false,],
    '/user/register'               => ['controller' => 'UserController',    'action' => 'register',              'auth' => false,],
    '/user/login_by_pwd'           => ['controller' => 'UserController',    'action' => 'login_by_pwd',          'auth' => false,],
    '/user/login_by_email'         => ['controller' => 'UserController',    'action' => 'login_by_email',        'auth' => false,],
    '/user/verify_token'           => ['controller' => 'UserController',    'action' => 'verify_token',          'auth' => false,],

    '/test'     => ['controller' => 'TestController',    'action' => 'test',    'auth' => true,],

    '/test1'    => ['controller' => 'TestController',    'action' => 'test1',   'auth' => false,],
];