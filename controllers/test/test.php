<?php
$ctx = \Swoole\Coroutine::getContext();
$response = $ctx['response'];
$data=$ctx['req'];

$kk=$ctx['user_id'];


require_once BASE_PATH . '/core/Utils.php';
use Core\Utils;
$k=Utils::randomNumber(10);

$d=['status'=>1,'msg'=>'成功test','data'=>$kk];
Utils::jsonResponse($response,$d);
return;