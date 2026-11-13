<?php
// index.php

require_once BASE_PATH . '/core/HttpResponse.php';
require_once BASE_PATH . '/core/AuthMiddleware.php';


function handleRequest($router,$pdo){
    //获取上下文
    $ctx = \Swoole\Coroutine::getContext();
    $response = $ctx['response'];
    $req=$ctx['req'];
    $redisPool = $ctx['redisPool'];

    //获取uri,若请求网址是'http://localhost:81/test?token=dlsafdfldjl',则$uri等于'/test
    $uri = $req['request_uri'];
    //通过uri去路由表查表，获取目标文件地址
    $route = $router->direct($uri);

    // 路由不存在
    if (!$route) {
        \Core\HttpResponse::jsonResponse(
            $response,
            ['status' => 0, 'msg' => '路由不存在'],
            404
        );
        return;
    }

    // 获取控制器和方法
    $controller = $route['controller'] ?? '';
    $action = $route['action'] ?? '';

    // 检查路由配置
    if (!$controller || !$action) {
        \Core\HttpResponse::jsonResponse(
            $response,
            ['status' => 0, 'msg' => '路由配置错误'],
            500
        );
        return;
    }


    // 先鉴权，通过后才执行业务文件，$route['auth']为true的要鉴权
    if (!empty($route['auth'])) {
        $authMiddleware  = new AuthMiddleware();
        $i = $authMiddleware ->handle($pdo);
        //存储user_id
        $ctx['user_id']=$i['id'];
    }
    // 控制器文件路径
    $controllerFile = BASE_PATH . '/controllers/' . $controller . '.php';

    // 检查控制器文件
    if (!is_file($controllerFile)) {
        \Core\HttpResponse::jsonResponse(
            $response,
            ['status' => 0, 'msg' => '控制器不存在'],
            500
        );
        return;
    }

    require_once $controllerFile;

    // 检查控制器类
    if (!class_exists($controller)) {
        \Core\HttpResponse::jsonResponse(
            $response,
            ['status' => 0, 'msg' => '控制器类不存在'],
            500
        );
        return;
    }

    // 创建控制器实例
    $instance = new $controller();

    // 检查方法是否存在，以及是否可以公开调用
    if (
        !method_exists($instance, $action) ||
        !(new ReflectionMethod($instance, $action))->isPublic() ||
        str_starts_with($action, '__')
    ) {
        \Core\HttpResponse::jsonResponse(
            $response,
            ['status' => 0, 'msg' => '业务方法不存在'],
            404
            );
        return;
    }

    // 调用业务方法
    $result = $instance->$action($pdo, $req,$response,$redisPool);

    // 返回 JSON
    // \Core\HttpResponse::jsonResponse(
    //     $response,
    //     $result
    // );



}
