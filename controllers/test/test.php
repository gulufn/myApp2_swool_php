<?php

require_once BASE_PATH . '/core/Utils.php';
// use Core\Utils;

$ctx = \Swoole\Coroutine::getContext();
$response = $ctx['response'];

$d=['status'=>1,'msg'=>'成功']
Utils::jsonResponse($response,$d);
