<?php
header('Content-Type: application/json');
Header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE');
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Origin:*');   //跨域

require_once BASE_PATH . '/config/mysql_init.php';
require_once BASE_PATH  . '/core/Utils.php';
require_once BASE_PATH  . '/core/Crypto.php';
require_once BASE_PATH  . '/core/Db.php';

use Core\Utils;
$utils = new Utils;
use Core\Crypto;
$crypto = new Crypto;
use Core\Db;
$db = new Db;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json=file_get_contents('php://input');
    $response = array('status' => 0,'msg' => '初始化');

    $data=$utils->judgePasswordLessLoginForm($json);
    if($data['status']===0){
        echo json_encode($data);
        exit();
    }

    $email=$data['email'];
    $picture_captcha=$data['picture_captcha'];
    $email_captcha=$data['email_captcha'];
    $key=$data['key'];

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->select(1);

    if($picture_captcha != $redis->get($key)){
        $response = array('status' => 0,'msg' =>'图片验证码错误，请换一张');
        $redis->del($key);
        echo json_encode($response);    
        exit();
    }


     $captcha_data=$db->select_data_by_meail_users($pdo,$email,['captcha','captcha_update_time','pwd']);

    if($captcha_data['code'] == 0){
        $response = array('status' => 0,'msg' =>'查询失败');
        echo json_encode($response);    
        exit();
    }
    //有可能数据库里还没有此邮箱的记录，有记录就进来
    if($captcha_data['data']){
        if($captcha_data['data']['pwd'] == ''){
            $response = array('status' => 0,'msg' =>'还未注册，请到注册页面注册');
            echo json_encode($response);    
            exit();
        }
        $seconds = time() - strtotime($captcha_data['data']['captcha_update_time']);
        if($seconds > 90){
            $response = array('status' => 0,'msg' =>'邮箱验证码过期了');
            echo json_encode($response);    
            exit();
        }
        if($captcha_data['data']['captcha'] != $email_captcha){
            $response = array('status' => 0,'msg' =>'邮箱验证码错误');
            echo json_encode($response);    
            exit();
        }
        $token=$utils->get_Token();
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $r = $db->update_data_users($pdo,$email,['token'=>$token,'token_update_time'=>$now,'register_time'=>$now]);
        if($r['code']==0){
            $response = array('status' => 0,'msg' =>'sql更新失败');
            echo json_encode($response);    
            exit();
        }
        $response = array('status' => 1,'msg' =>'登录成功');
        echo json_encode($response);    
        exit();
    }else{
        $response = array('status' => 0,'msg' =>'还未注册，请到注册页面注册2');
        echo json_encode($response); 
    }
}else{
    $response = array('status' => 0,'msg' => '不要使用get请求');
    echo json_encode($response);
}