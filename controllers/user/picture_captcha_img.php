<?php
require_once BASE_PATH  . '/core/Crypto.php';
use Core\Crypto;


$token = $req['get']['token'] ?? '';
// $captcha=Crypto::decrypt($token);
$captcha=$token;
function generateCaptcha($c): string {
    $img=imagecreate(60,20);
    $color=imagecolorallocate($img,rand(0,255),rand(0,255),rand(0,255));    //画布背景颜色
    $white=imagecolorallocate($img,255,255,255);     //相当于画笔
    imageline($img,rand(0,60),rand(0,20),rand(0,60),rand(0,20),$white);     //画一条白线
    imageline($img,rand(0,60),rand(0,20),rand(0,60),rand(0,20),$white);
    imageline($img,rand(0,60),rand(0,20),rand(0,60),rand(0,20),$white);
    imageline($img,rand(0,60),rand(0,20),rand(0,60),rand(0,20),$white);
    // imagearc($img,250,100,150,150,0,360,$white);   //画一个圆
    // imagerectangle($img,350,20,500,170,$white);   //画一个正方形

    $x=random_int(-5,5);
    $y=random_int(-5,5);

    imagestring($img, 5, $x, $y, $c, $white);

    ob_start();
    imagegif($img);            // 输出到缓冲区
    $data = ob_get_clean();    // 取出二进制字符串
    imagedestroy($img);
    return $data;              // 返回图片二进制
}


\Core\HttpResponse::imageResponse($response,generateCaptcha($captcha));
return;

// $rdata=['status'=>0,'msg'=>'获取图片验证码信息','img_url'=>$img_url,'key'=>$token];
// \Core\HttpResponse::jsonResponse($response,$rdata);
// return;
