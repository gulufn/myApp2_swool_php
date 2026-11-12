<?php
// index.php

require_once BASE_PATH . '/core/Utils.php';
require_once BASE_PATH . '/core/AuthMiddleware.php';
use Core\Utils;

function handleRequest($router,$pdo){
    //获取上下文
    $ctx = \Swoole\Coroutine::getContext();
    $response = $ctx['response'];
    $req=$ctx['req'];

    //获取uri,若请求网址是'http://localhost:81/test?token=dlsafdfldjl',则$uri等于'/test
    $uri = $req['request_uri'];
    //通过uri去路由表查表，获取目标文件地址
    $route = $router->direct($uri);

    $file = $route['file'] ?? null;                      //提取php文件地址
    //文件不存在就返回404
    if (!$file || !file_exists(BASE_PATH . '/' . $file)) {
        $rdata=['status'=>0,'msg'=>'路由不存在'];
        Utils::jsonResponse($response,$rdata,404);
        return;
    }

    // 先鉴权，通过后才执行业务文件，$route['auth']为true的要鉴权
    if (!empty($route['auth'])) {
        $authMiddleware  = new AuthMiddleware();
        $i = $authMiddleware ->handle($pdo);
        //存储user_id
        $ctx['user_id']=$i['id'];
    }
    //跳转到这个文件
    require BASE_PATH . '/' . $file;
}
