<?php
header('Content-Type: application/json');
Header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE');
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Origin:*');   //跨域

require_once BASE_PATH . '/config/mysql_init.php';
require_once BASE_PATH  . '/core/Db.php';

use Core\Db;
$db = new Db;

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json=file_get_contents('php://input');
    $response = array('status' => 0,'msg' => '不要使用post请求');

}else{
    $r=$db->select_data_by_token_users($pdo,$token,['email','token_update_time']);
    if($r['code']==0){
        $response = array('status' => 0,'msg' =>'token查询失败');
        echo json_encode($response);    
        exit();
    }
    $seconds = time() - strtotime($r['data']['token_update_time']);
    if($seconds > 60*60*24){
        $response = array('status' => 0,'msg' =>'token已过期，请重新登录');
        echo json_encode($response);    
        exit();
    }
    $rdata=['email'=>$r['data']['email']];
    $response = array('status' => 1,'msg' => '成功','data'=>$rdata);
    echo json_encode($response);
}