<?php
require_once BASE_PATH . '/core/HttpResponse.php';

class TestController
{
    // 不需要鉴权的接口
    public function test1($pdo, $req,$response,$redisPool)
    {
        $data = [
            'status' => 1,
            'msg' => 'test1 请求成功',
        ];
        \Core\HttpResponse::jsonResponse($response,$data);
        return;
    }

    // 需要鉴权的接口
    public function test($pdo, $req,$response,$redisPool)
    {
        // $ctx = \Swoole\Coroutine::getContext();

        // $userId = $ctx['user_id'];

        $data = [
            'status' => 1,
            'msg' => 'test 请求成功',
            'user_id' => 'l',
        ];

        \Core\HttpResponse::jsonResponse($response,$data);
        return;
    }
}