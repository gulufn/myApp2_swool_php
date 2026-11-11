<?php
// swoole_server.php

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

define('BASE_PATH', __DIR__);
require_once BASE_PATH  . '/index.php';
// require_once BASE_PATH . '/config/mysql_init.php';
// require_once BASE_PATH . '/core/Db.php';
require_once BASE_PATH . '/core/Utils.php';
use Core\Utils;

$server = new Server('127.0.0.1', 9501);
$server->set([
    'worker_num' => 2,
    'enable_coroutine' => true,
]);

$server->on('Request', function (Request $request, Response $response) {
    // 1. 统一 CORS 响应头（所有响应都带）
    $response->header('Access-Control-Allow-Origin', '*');
    $response->header('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Content-Type,Authorization,X-Requested-With');
    $response->header('Access-Control-Max-Age', '86400');   // 预检结果缓存 1 天，减少预检次数

    // 2. 预检请求直接返回，不进业务
    if ($request->server['request_method'] === 'OPTIONS') {
        $response->status(204);
        $response->end();
        return;
    }

    // 每个请求协程独立取上下文，不会互相串
    $ctx = \Swoole\Coroutine::getContext();
    $ctx['response'] = $response;      // 绑定到当前协程

    //$request里所有的数据，可以通过回调查看
    // $data = [
    //     'server' => $request->server,
    //     'header' => $request->header,
    //     'get' => $request->get,
    //     'post' => $request->post,
    //     'cookie' => $request->cookie,
    //     'files' => $request->files,
    //     'raw_content' => $request->rawContent(),
    // ];
    $data=[
        'query_string'=>$request->server['query_string'] ?? '',              //网址里'?'后面的参数
        'request_method'=>$request->server['request_method'] ?? '',          //客户端的请求方法：GET | POST | PUT 等
        'request_uri'=>$request->server['request_uri'] ?? '',                //端口后面到'?'前面的那段，例如http://127.0.0.1:82/test?token=123，这里取出来的是'/test'
        'request_time_float'=>$request->server['request_time_float'] ?? 0.0, //请求到达 Swoole 的时间，不是"开始处理"的时间
        'remote_addr'=>$request->server['remote_addr'] ?? '',                //客户端的ip地址
        'authorization'=>$request->header['authorization'] ?? '',            //客户端放token的位置，'authorization': 'Bearer b86aca2a9719fa08f3OnSYrNOl1oBhXrQS'
        'x-forwarded-server'=>$request->header['x-forwarded-server'] ?? '',  //代理/网关加的头，表示"处理原始请求的那台服务器/代理的机器名"
        'raw_content' => $request->rawContent() ?? '',
    ];
    $ctx['data']=$data;

    //业务
    
    handleRequest($data,$response);
    // require BASE_PATH . 'index.php';
    //业务
    
    // Utils::jsonResponse($response,$data);   //这个代替后续的响应
    // $response->header('Content-Type','application/json; charset=utf-8');
    // $response->end(
    //     json_encode(
    //         $data,
    //         JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    //     )
    // );
});

$server->start();