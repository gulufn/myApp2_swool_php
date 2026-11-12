<?php
// swoole_server.php     反向代理服务器
//use 给类起短名/别名，类文件来自扩展
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
//数据库连接池类
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
//连接池配置，
$pool = new PDOPool(
    (new PDOConfig())
        ->withHost('127.0.0.1')     //数据库主机地址
        ->withPort(3306)            //数据库主机
        ->withDbName('my_demo')     //数据库名
        ->withUsername('root')      //数据库用户名
        ->withPassword('root')      //数据库密码
        ->withCharset('utf8mb4')    //数据库编码，和建库是的编码统一
        ->withOptions([
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,    //设置模式为异常
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,          //设置返回数组是关联数组
        ]),
    16                              //最大容纳16个连接
);

//设置错误处理模式(ATTR_ERRMODE)，PDO::ERRMODE_EXCEPTION（异常模式）,PDO::ERRMODE_SILENT（沉默，不警告不报错），PDO::ERRMODE_WARNING（警告模式）
//事务是否自动提交(ATTR_AUTOCOMMIT)，0是关闭，1是自动,老版本可能没有该属性，可以注释掉
//设置结果集返回格式(ATTR_DEFAULT_FETCH_MODE)     PDO::FETCH_ASSOC（关联数组）  PDO::FETCH_NUM（索引数组）  PDO::FETCH_BOTH（两种数组都返回）  PDO::FETCH_OBJ（对象）

define('BASE_PATH', __DIR__);                   //定义根路径
require_once BASE_PATH  . '/index.php';         //导入入口文件
require_once BASE_PATH . '/core/Router.php';    //导入路由类
$routers = require BASE_PATH . '/routes.php';   //导入路由表

$router = new Router;                           //new Router类
$router->define($routers);                      //调用方法加载路由表到路由类里

$server = new Server('127.0.0.1', 9501);        //创建服务器
$server->set([                                  //负责“处理请求”的进程个数（一个处理器核心最多设置4个）
    'worker_num' => 8,
    'enable_coroutine' => true,                 //让 Worker 内能用协程并发处理请求/IO,必须开启，不开启就只能排队，没并发
]);

$server->on('Request', function (Request $request, Response $response) use ($router,$pool) {
    //设置预检
    $response->header('Access-Control-Allow-Origin', '*');  //允许任何来源的页面跨域读取这个响应
    $response->header('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS'); //预检时告诉浏览器：允许跨域用这些方法
    $response->header('Access-Control-Allow-Headers', 'Content-Type,Authorization,X-Requested-With');  //预检时告诉浏览器：允许跨域带这些请求头
    $response->header('Access-Control-Max-Age', '86400');     //有过预检的状态保存24小时，可以减少预测次数
    //预检请求直接返回，不进业务
    if ($request->server['request_method'] === 'OPTIONS') {
        $response->status(204);
        $response->end();
        return;
    }
    //设置自己的req,方便使用
    $req=[
        'query_string'      => $request->server['query_string'] ?? '',
        'request_method'    => $request->server['request_method'] ?? '',
        'request_uri'       => $request->server['request_uri'] ?? '',
        'request_time_float'=> $request->server['request_time_float'] ?? 0.0,
        'remote_addr'       => $request->server['remote_addr'] ?? '',
        'authorization'     => $request->header['authorization'] ?? '',
        'x-forwarded-server'=> $request->header['x-forwarded-server'] ?? '',
        'raw_content'       => $request->rawContent() ?? '',
        'get'               => $request->get ?? [],
        'post'              => $request->post ?? [],
    ];
    $ctx = \Swoole\Coroutine::getContext();     //每个请求协程独立取上下文，不会互相串
    $ctx['response'] = $response;               //把业务要使用的数据保存到上下文里
    $ctx['req']=$req;                           //把业务要使用的数据保存到上下文里
    
    $pdo = $pool->get();                        //从连接池里获取连接
    try {
        handleRequest($router,$pdo);            //入口方法
    } finally {
        $pool->put($pdo);                       //用完后把连接放回连接池
    }

    // //设置响应类型，json格式
    // $response->header('Content-Type','application/json; charset=utf-8');
    // 响应的是json, 中文等非 ASCII 字符不转义，原样输出 | 输出带缩进换行的格式化 JSON，便于阅读,
    // $response->end(json_encode($req,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
});

$server->start();