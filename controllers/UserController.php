<?php
require_once BASE_PATH . '/core/HttpResponse.php';
require_once BASE_PATH  . '/core/Crypto.php';
require_once BASE_PATH  . '/core/Utils.php';
require_once BASE_PATH  . '/core/Db.php';
require_once BASE_PATH . '/config/send_email_init.php';

use Core\Crypto;
use Core\Utils;
use Core\Db;

class UserController
{
    // 响应关于图片验证的信息
    public function get_picture_captcha($pdo, $req,$response,$redisPool){
        //图片的验证码，4位数
        $captcha=Utils::randomNumber(4);

        //图片验证码，加密
        $i=Crypto::encrypt($captcha);
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
            $rdata=['status'=>1,'msg'=>'获取图片验证码信息','img_url'=>$img_url,'key'=>$key];
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }else{
            $rdata=['status'=>0,'msg'=>'请使用get请求'];
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }
    }

    //图片接口，接收加密的验证码，解密后渲染返回图片
    public function picture_captcha_img($pdo, $req,$response,$redisPool){
        //获取网址里的参数
        $token = $req['get']['token'] ?? '';
        //解密
        $captcha=Crypto::decrypt($token);
        //把文本画出来
        $img = Utils::generateCaptcha($captcha);
        //使用图片格式响应
        \Core\HttpResponse::imageResponse($response,$img);
        return;
    }
    //获取邮箱验证码
    public function get_email_captcha($pdo, $req,$response,$redisPool){
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $rdata=Utils::judgeEmail($json);
            if($rdata['status']===0){
                \Core\HttpResponse::jsonResponse($response,$rdata);      //邮箱格式不合法
                return;
            }
            $email = $rdata['email'];
            
            $select_email=Db::select_data_by_meail_users($pdo,$email,['captcha_update_time']);
            //查看这个数据库里是否有这个邮箱的信息
            if($select_email['code']==0){
                $rdata = array('status' => 0,'msg' => '查询sql失败');
                \Core\HttpResponse::jsonResponse($response,$rdata,500);
                return;
                
            }
            //有信息，说明之前获取过验证码或注册过
            if($select_email['data']){
                
                //限制获取频率
                $seconds = time() - strtotime($data['data']['captcha_update_time']);
                if($seconds < 60){
                    $rdata=array('status' => 0,'msg' => '验证码获取太频繁，1分钟后重试');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $captcha=Utils::randomNumber(4);
                $send_count='您的验证码为 '.$captcha.',若非本人操作请忽略此消息';
                $nickname='诺言非';                                                 //发件人对外的昵称
                
                $data = send_to_email($send_count,$nickname,$email);          //发送邮件

                if($data===0){             //发送失败
                    $rdata=array('status' => 0,'msg' => '邮件发送失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata,500);
                    return;
                }
                
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $r=Db::update_data_users($pdo,$email,['captcha'=>$captcha,'captcha_update_time'=>$now]);  //更新验证码
                if($r['code']==0){
                    $rdata=array('status' => 0,'msg' => '更新失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata,500);
                    return;
                }
                $rdata=array('status' => 1,'msg' => '更新成功');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }else{
                $captcha=Utils::randomNumber(4);
                $send_count='您的验证码为 '.$captcha.',若非本人操作请忽略此消息';
                $nickname='诺言非';                                                 //发件人对外的昵称
                
                $data = send_to_email($send_count,$nickname,$email);          //发送邮件

                if($data===0){             //发送失败
                    $rdata=array('status' => 0,'msg' => '邮件发送失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $r=Db::insert_data_users($pdo,['email'=>$email,'captcha'=>$captcha,'captcha_update_time'=>$now]);
                if($r==0){
                    $rdata=array('status' => 0,'msg' => '插入失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata,500);
                    return;
                }
                $rdata=array('status' => 1,'msg' => '插入成功');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
        }else{
            $rdata=array('status' => 0,'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }
    }
    //注册
    public function register($pdo, $req,$response,$redisPool){
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $rdata=Utils::judgeRegisterForm($json);
            if($rdata['status']===0){
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }

            $email=$rdata['email'];
            $picture_captcha=$rdata['picture_captcha'];
            $pwd=$rdata['pwd'];
            $email_captcha=$rdata['email_captcha'];
            $key=$rdata['key'];

            //从redis连接池里拿一个连接
            $redis = $redisPool->get();
            try {
                $redis->select(1);   //注册页面保存图片验证码使用第一个redis库
                if($picture_captcha != $redis->get($key)){
                    $rdata = array('status' => 0,'msg' =>'图片验证码错误，请换一张');
                    $redis->del($key);
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
            } finally {
                $redisPool->put($redis);   // 必须归还
            }

            
            
            $data = Db::select_data_by_meail_users($pdo,$email,['captcha','captcha_update_time','pwd']);
            if($data['code']==0){
                $rdata = array('status' => 0,'msg' =>'查询验证码失败');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
            //有可能数据库里还没有此邮箱的记录，有记录就进来
            if($data['data']){
                if($data['data']['pwd'] != ''){
                    $rdata = array('status' => 0,'msg' =>'当前邮箱已被注册过了');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $seconds = time() - strtotime($data['data']['captcha_update_time']);
                if($seconds>90){
                    $rdata = array('status' => 0,'msg' =>'邮箱验证码过期，请重新获取验证码');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                if($email_captcha != $data['data']['captcha']){
                    $rdata = array('status' => 0,'msg' =>'邮箱验证码错误');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                //前面的验证都通过了，开始插入数据
                $pwd=hash('sha256',$pwd.'jiaoyan');
                $token=Utils::get_Token();
                $now = (new DateTime())->format('Y-m-d H:i:s');
                
                $r = Db::update_data_users($pdo,$email,['pwd'=>$pwd,'token'=>$token,'token_update_time'=>$now,'register_time'=>$now]);
                if($r['code']==0){
                    $rdata = array('status' => 0,'msg' =>'注册sql失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata,500);
                    return;
                }
                $rdata = array('status' => 1,'msg' =>'注册成功','token'=>$token);
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }else{
                
                $rdata = array('status' => 0,'msg' =>'还未获取验证码');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
        }else{
            $rdata = array('status' => 0,'msg' => '请使用使用post请求');
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }

    }

    public function login_by_pwd($pdo, $req,$response,$redisPool){
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];
            

            $data = Utils::judgePwdLoginForm($json);
            if($data['status']===0){
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $email=$data['email'];
            $picture_captcha=$data['picture_captcha'];
            $pwd=$data['pwd'];
            $key=$data['key'];

            //从redis连接池里拿一个连接
            $redis = $redisPool->get();
            try {
                $redis->select(1);   //注册页面保存图片验证码使用第一个redis库
                if($picture_captcha != $redis->get($key)){
                    $rdata = array('status' => 0,'msg' =>'图片验证码错误，请换一张');
                    $redis->del($key);
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
            } finally {
                $redisPool->put($redis);   // 必须归还
            }

            $pwd_data = Db::select_data_by_meail_users($pdo,$email,['pwd']);
            if($pwd_data['code'] == 0){
                $rdata = array('status' => 0,'msg' =>'查询密码失败');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
            //有可能数据库里还没有此邮箱的记录，有记录就进来
            if($pwd_data['data']){
                if($pwd_data['data']['pwd'] == ''){
                    $rdata = array('status' => 0,'msg' =>'还未注册，请到注册页面注册');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                if(hash('sha256',$pwd.'jiaoyan') != $pwd_data['data']['pwd']){
                    $rdata = array('status' => 0,'msg' =>'密码错误');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $token = Utils::get_Token();
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $r = Db::update_data_users($pdo,$email,['token'=>$token,'token_update_time'=>$now]);
                if($r['code']==0){
                    $rdata = array('status' => 0,'msg' =>'登录sql失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $rdata = array('status' => 1,'msg' =>'登录成功','token'=>$token);
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }else{
                $rdata = array('status' => 0,'msg' =>'还未注册，请到注册页面注册');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
                
        }else{
            $rdata = array('status' => 0,'msg' => '请使用使用post请求');
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }
    }

    public function login_by_email($pdo, $req,$response,$redisPool){
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $data = Utils::judgePasswordLessLoginForm($json);
            if($data['status']===0){
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $email=$data['email'];
            $picture_captcha=$data['picture_captcha'];
            $email_captcha=$data['email_captcha'];
            $key=$data['key'];

            //从redis连接池里拿一个连接
            $redis = $redisPool->get();
            try {
                $redis->select(1);   //注册页面保存图片验证码使用第一个redis库
                if($picture_captcha != $redis->get($key)){
                    $rdata = array('status' => 0,'msg' =>'图片验证码错误，请换一张');
                    $redis->del($key);
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
            } finally {
                $redisPool->put($redis);   // 必须归还
            }


            $captcha_data = Db::select_data_by_meail_users($pdo,$email,['captcha','captcha_update_time','pwd']);

            if($captcha_data['code'] == 0){
                $rdata = array('status' => 0,'msg' =>'查询失败');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
            //有可能数据库里还没有此邮箱的记录，有记录就进来
            if($captcha_data['data']){
                if($captcha_data['data']['pwd'] == ''){
                    $rdata = array('status' => 0,'msg' =>'还未注册，请到注册页面注册');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $seconds = time() - strtotime($captcha_data['data']['captcha_update_time']);
                if($seconds > 90){
                    $rdata = array('status' => 0,'msg' =>'邮箱验证码过期了');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                if($captcha_data['data']['captcha'] != $email_captcha){
                    $rdata = array('status' => 0,'msg' =>'邮箱验证码错误');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $token = Utils::get_Token();
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $r = Db::update_data_users($pdo,$email,['token'=>$token,'token_update_time'=>$now,'register_time'=>$now]);
                if($r['code']==0){
                    $rdata = array('status' => 0,'msg' =>'sql更新失败');
                    \Core\HttpResponse::jsonResponse($response,$rdata);
                    return;
                }
                $rdata = array('status' => 1,'msg' =>'登录成功');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }else{
                $rdata = array('status' => 0,'msg' =>'还未注册，请到注册页面注册');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
        }else{
            $rdata = array('status' => 0,'msg' => '请使用使用post请求');
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }

    }

    public function verify_token($pdo, $req,$response,$redisPool){
        if ($req['request_method'] === 'GET') {
            //获取请求头中的authorization，（由客户端设置，一般放token）
            $header=$req['authorization'];
            //用正则提取token并返回，没匹配到就是false
            if (preg_match('/Bearer\s(\S+)/i', $header, $m)) {
                $token = $m[1];
            }
            
            $r = Db::select_data_by_token_users($pdo,$token,['email','token_update_time']);
            if($r['code']==0){
                $rdata = array('status' => 0,'msg' =>'token查询失败');
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
            $seconds = time() - strtotime($r['data']['token_update_time']);
            if($seconds > 60*60*24){
                $rdata = array('status' => 0,'msg' =>'token已过期，请重新登3录','t'=>$token);
                \Core\HttpResponse::jsonResponse($response,$rdata);
                return;
            }
            $rdata=['email'=>$r['data']['email']];
            $rdata = array('status' => 1,'msg' => '成功','data'=>$rdata);
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;

        }else{
            $rdata = array('status' => 0,'msg' => '请使用使用get请求');
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;
        }
    }
}