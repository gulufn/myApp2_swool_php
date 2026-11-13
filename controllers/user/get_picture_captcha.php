<?php
require_once BASE_PATH  . '/core/Crypto.php';
require_once BASE_PATH  . '/core/Utils.php';
use Core\Crypto;
use Core\Utils;



//图片的验证码，4位数
$captcha=Utils::randomNumber(4);

//图片验证码，加密
$i=Crypto::encrypt($captcha);
$i=$captcha;
$img_url='/user/picture_captcha_img?token='.$i;

//生成随机字符串
$i=Utils::get_Token(10);
$key=hash('sha256',time().$i);

//从redis连接池里拿一个连接
$redis = $redisPool->get();
try {
    $redis->select(1);   //注册页面保存图片验证码使用第一个redis库
    $redis->set($key, $captcha, ['ex' => 600]);
} finally {
    $redisPool->put($redis);   // 必须归还
}

if ($req['request_method'] === 'GET') {
    $rdata=['status'=>0,'msg'=>'获取图片验证码信息','img_url'=>$img_url,'key'=>$key];
    \Core\HttpResponse::jsonResponse($response,$rdata);
    return;
}else{
    $rdata=['status'=>0,'msg'=>'请使用get请求','t'=>$ctx];
    \Core\HttpResponse::jsonResponse($response,$rdata);
    return;
}