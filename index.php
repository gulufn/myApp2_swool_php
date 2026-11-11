<?php
// // index.php
// require_once BASE_PATH . '/core/Utils.php';
// require_once BASE_PATH . '/core/Router.php';            //加载路由类
// require_once BASE_PATH . '/routes.php';                //加载路由表，数组名为$routers
// // require_once BASE_PATH '/core/AuthMiddleware.php';    //加载token鉴权类

// use Core\Utils;
// $router = new Router;                            //实例化路由类
// $router->define($routers);                        //把路由表加载到路由类里去，执行的是一个赋值方法


// function handleRequest($data): array
// {
//     $ctx = \Swoole\Coroutine::getContext();
//     $response = $ctx['response'];
//     $uri=$data['request_uri'];
//     $route = $router->direct($uri);                //执行路由匹配，命中则返回 ['file'=>'php文件地址','auth' => bool]，失败返回null
//     $file=$route['file'] ?? null;
//     //文件不存在就返回404
//     if (!$file || !file_exists(BASE_PATH . '/' . $file)) {
//         $d=['status'=>0,'msg'=>'网址错误'];
//         Utils::jsonResponse($response,$d,404);
//     }
    
//     //跳转到这个文件
//     require BASE_PATH . '/' . $file;
// }


// require_once BASE_PATH . '/core/Router.php';            //加载路由类
// // require_once BASE_PATH . '/core/AuthMiddleware.php';    //加载token鉴权类
// require_once BASE_PATH . '/routes.php';                 //加载路由表，数组名为$routers
// $ctx = \Swoole\Coroutine::getContext();
// $response = $ctx['response'];
// $data=$ctx['data'];
// $router = new Router;                            //实例化路由类
// $router->define($routers);                        //把路由表加载到路由类里去，执行的是一个赋值方法
// require_once BASE_PATH . '/core/Utils.php';

// // $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));      //parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))等于'/test'。功能就是获取端口后面，问号前面的字符串
// $uri=$data['request_uri'];
// $route = $router->direct($uri);                  //执行路由匹配，命中则返回 ['file'=>'php文件地址','auth' => bool]，失败返回null

// // 兼容：若 direct 返回的是字符串，视为公开接口
// if (is_string($route)) {
//     $route = ['file' => $route, 'auth' => false];
// }

// $file = $route['file'] ?? null;                      //提取php文件地址
// //文件不存在就返回404
// if (!$file || !file_exists(__DIR__ . '/' . $file)) {
//     $d=['status'=>0,'msg'=>'网址错误'];
//     Utils::jsonResponse($response,$d,404);
// }


// //跳转到这个文件
// require BASE_PATH . '/' . $file;

function handleRequest($data,$response){
    require_once BASE_PATH . '/core/Utils.php';
    // use Core\Utils;

    $d=['status'=>1,'msg'=>'成功'];
    Utils::jsonResponse($response,$d);
}

