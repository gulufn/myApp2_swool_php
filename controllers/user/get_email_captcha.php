<?php
header('Content-Type: application/json');
Header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE');
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Origin:*');   //跨域
require_once BASE_PATH  . '/core/Db.php';
require_once BASE_PATH  . '/core/Utils.php';
require_once BASE_PATH . '/config/mysql_init.php';
require_once BASE_PATH . '/config/send_email_init.php';

use Core\Db;
$db = new Db;
use Core\Utils;
$utils = new Utils;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json=file_get_contents('php://input');
    $response = array('status' => 0,'msg' => '初始化');

    $data=$utils->judgeEmail($json);
    if($data['status']===0){
        echo json_encode($data);    //邮箱格式不合法
        exit();
    }
    $email = $data['email'];
    
    $select_email=$db->select_data_by_meail_users($pdo,$email,['captcha_update_time']);
    //查看这个数据库里是否有这个邮箱的信息
    if($select_email['code']==0){
        $response = array('status' => 0,'msg' => '查询sql失败');
        echo json_encode($response);
        exit();
    }
    //有信息，说明之前获取过验证码或注册过
    if($select_email['data']){
        
        //限制获取频率
        $seconds = time() - strtotime($data['data']['captcha_update_time']);
        if($seconds < 60){
            $response=array('status' => 0,'msg' => '验证码获取太频繁，1分钟后重试');
            echo json_encode($response);
            exit();
        }
        $captcha=$utils->randomNumber(4);
        $send_count='您的验证码为 '.$captcha.',若非本人操作请忽略此消息';
        $nickname='诺言非';                                                 //发件人对外的昵称
        
        $data = send_to_email($send_count,$nickname,$email);          //发送邮件

        if($data===0){             //发送失败
            $response=array('status' => 0,'msg' => '邮件发送失败');
            echo json_encode($response);
            exit();
        }
        
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $r=$db->update_data_users($pdo,$email,['captcha'=>$captcha,'captcha_update_time'=>$now]);  //更新验证码
        if($r['code']==0){
            $response=array('status' => 0,'msg' => '更新失败');
            echo json_encode($response);
            exit();
        }
        $response=array('status' => 1,'msg' => '更新成功');
        echo json_encode($response);
               
    }else{
        $captcha=$utils->randomNumber(4);
        $send_count='您的验证码为 '.$captcha.',若非本人操作请忽略此消息';
        $nickname='诺言非';                                                 //发件人对外的昵称
        
        $data = send_to_email($send_count,$nickname,$email);          //发送邮件

        if($data===0){             //发送失败
            $response=array('status' => 0,'msg' => '邮件发送失败');
            echo json_encode($response);
            exit();
        }
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $r=$db->insert_data_users($pdo,['email'=>$email,'captcha'=>$captcha,'captcha_update_time'=>$now]);
        if($r==0){
            $response=array('status' => 0,'msg' => '插入失败');
            echo json_encode($response);
            exit();
        }
        $response=array('status' => 1,'msg' => '插入成功');
        echo json_encode($response);
    }
}else{
    $response = array('status' => 0,'msg' => '不要使用get请求');
    echo json_encode($response);
}