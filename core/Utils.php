<?php
//Utils.php
namespace Core;
class Utils{
    //输出$length长度的随机数字符串
    public static function randomNumber(int $length):string{
        $captcha='';
        for($i=0;$i<$length;$i++){
            $captcha=$captcha.(string)mt_rand(0,9);
        }
        return $captcha;
    }
    //生成token
    public static function get_Token(int $randomLength = 16): string{
        // 前段：时间戳哈希
        $timePart = hash('sha256', (string)microtime(true));

        // 后段：随机字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = strlen($chars) - 1;
        $randPart = '';
        for ($i = 0; $i < $randomLength; $i++) {
            $randPart .= $chars[random_int(0, $max)];
        }
        return $timePart . $randPart;
    }
    //输出图片的二进制数据流
    public static function generateCaptcha($c): string {
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
    //验证邮箱是否合法
    public static function judgeEmail(string $json): array{
        $post_json=json_decode($json,true);
        if (!is_array($post_json) || array_is_list($post_json)) {
            $response=array('status' => 0, 'msg' => 'JSON 格式错误');
            return $response;
        }
        $email = $post_json['email'] ?? null;
         if($email === null || !is_string($email) || trim($email) === ''){
            $response=array('status' => 0, 'msg' => 'email不能为空');
            return $response;
        }
        // $email = trim($email);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $response=array('status' => 0, 'msg' => 'email不合法');
            return $response;
        }
        return array('status' => 1, 'msg' => '合法','email'=>$email);
    }
    //验证注册表单是否合法
    public static function judgeRegisterForm(string $json){
        $post_json=json_decode($json,true);
        if (!is_array($post_json) || array_is_list($post_json)) {
            $response=array('status' => 0, 'msg' => 'JSON 格式错误');
            return $response;
        }
        $email = $post_json['email'] ?? null;
         if($email === null || !is_string($email) || trim($email) === ''){
            $response=array('status' => 0, 'msg' => 'email不能为空');
            return $response;
        }
        // $email = trim($email);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $response=array('status' => 0, 'msg' => 'email不合法');
            return $response;
        }

        $picture_captcha=$post_json['picture_captcha'] ?? null;
        if($picture_captcha === null || !is_string($picture_captcha) || trim($picture_captcha) === '' || mb_strlen($picture_captcha) >6){
            $response=array('status' => 0, 'msg' => '图片验证码不能为空或验证码长度不合法');
            return $response;
        }
        $email_captcha=$post_json['email_captcha'] ?? null;
        if($email_captcha === null || !is_string($email_captcha) || trim($email_captcha) === '' || mb_strlen($email_captcha) >6){
            $response=array('status' => 0, 'msg' => '邮箱验证码不能为空或验证码长度不合法');
            return $response;
        }
        $pwd=$post_json['pwd'] ?? null;
        if($pwd === null || !is_string($pwd) || trim($pwd) === '' || mb_strlen($pwd) > 20 || mb_strlen($pwd) < 6){
            $response=array('status' => 0, 'msg' => '密码不能为空或长度小于6或超过20位');
            return $response;
        }
        $key = $post_json['key'] ?? null;
        if($key === null || !is_string($key) || trim($key) === '' || mb_strlen($key) >128){
            $response=array('status' => 0, 'msg' => 'key不能为空或长度超过128位');
            return $response;
        }
        return array('status' => 1, 'msg' => '合法','email'=>$email,'pwd'=>$pwd,'email_captcha'=>$email_captcha,'picture_captcha'=>$picture_captcha,'key'=>$key);
    }
    //验证密码登录表单是否合法
    public static function judgePwdLoginForm(string $json){
        $post_json=json_decode($json,true);
        if (!is_array($post_json) || array_is_list($post_json)) {
            $response=array('status' => 0, 'msg' => 'JSON 格式错误');
            return $response;
        }
        $email = $post_json['email'] ?? null;
         if($email === null || !is_string($email) || trim($email) === ''){
            $response=array('status' => 0, 'msg' => 'email不能为空');
            return $response;
        }
        // $email = trim($email);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $response=array('status' => 0, 'msg' => 'email不合法');
            return $response;
        }
        $picture_captcha=$post_json['picture_captcha'] ?? null;
        if($picture_captcha === null || !is_string($picture_captcha) || trim($picture_captcha) === '' || mb_strlen($picture_captcha) >6){
            $response=array('status' => 0, 'msg' => '图片验证码不能为空或验证码长度不合法');
            return $response;
        }
        
        $pwd=$post_json['pwd'] ?? null;
        if($pwd === null || !is_string($pwd) || trim($pwd) === '' || mb_strlen($pwd) > 20 || mb_strlen($pwd) < 6){
            $response=array('status' => 0, 'msg' => '密码不能为空或长度小于6或超过20位');
            return $response;
        }
        $key = $post_json['key'] ?? null;
        if($key === null || !is_string($key) || trim($key) === '' || mb_strlen($key) >128){
            $response=array('status' => 0, 'msg' => 'key不能为空或长度超过128位');
            return $response;
        }
        return array('status' => 1, 'msg' => '合法','email'=>$email,'pwd'=>$pwd,'picture_captcha'=>$picture_captcha,'key'=>$key);
    }
    //验证免密登录 表单是否合法
    public static function judgePasswordLessLoginForm(string $json){
        $post_json=json_decode($json,true);
        if (!is_array($post_json) || array_is_list($post_json)) {
            $response=array('status' => 0, 'msg' => 'JSON 格式错误');
            return $response;
        }
        $email = $post_json['email'] ?? null;
         if($email === null || !is_string($email) || trim($email) === ''){
            $response=array('status' => 0, 'msg' => 'email不能为空');
            return $response;
        }
        // $email = trim($email);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $response=array('status' => 0, 'msg' => 'email不合法');
            return $response;
        }

        $picture_captcha=$post_json['picture_captcha'] ?? null;
        if($picture_captcha === null || !is_string($picture_captcha) || trim($picture_captcha) === '' || mb_strlen($picture_captcha) >6){
            $response=array('status' => 0, 'msg' => '图片验证码不能为空或验证码长度不合法');
            return $response;
        }
        $email_captcha=$post_json['email_captcha'] ?? null;
        if($email_captcha === null || !is_string($email_captcha) || trim($email_captcha) === '' || mb_strlen($email_captcha) >6){
            $response=array('status' => 0, 'msg' => '邮箱验证码不能为空或验证码长度不合法');
            return $response;
        }
        $key = $post_json['key'] ?? null;
        if($key === null || !is_string($key) || trim($key) === '' || mb_strlen($key) >128){
            $response=array('status' => 0, 'msg' => 'key不能为空或长度超过128位');
            return $response;
        }
        return array('status' => 1, 'msg' => '合法','email'=>$email,'email_captcha'=>$email_captcha,'picture_captcha'=>$picture_captcha,'key'=>$key);
    }
    public static function judge_user_setting(string $json,bool $p,bool $c){
        $post_json=json_decode($json,true);
        if (!is_array($post_json) || array_is_list($post_json)) {
            $response=array('status' => 0, 'msg' => 'JSON 格式错误');
            return $response;
        }
        $pwd = $post_json['pwd'] ?? null;
        if($p){
            if($pwd === null || !is_string($pwd) || trim($pwd) === '' || mb_strlen($pwd) >20 || mb_strlen($pwd) <6){
                $response=array('status' => 0, 'msg' => '密码不能为空或长度小于6或大于20');
                return $response;
            }
        }
        $new_pwd = $post_json['new_pwd'] ?? null;
        if($new_pwd === null || !is_string($new_pwd) || trim($new_pwd) === '' || mb_strlen($new_pwd) >20 || mb_strlen($new_pwd) <6){
            $response=array('status' => 0, 'msg' => '新密码不能为空或长度小于6或大于20');
            return $response;
        }
        $captcha_email = $post_json['captcha_email'] ?? null;
        if($c){
            if($captcha_email === null || !is_string($captcha_email) || trim($captcha_email) === '' || mb_strlen($captcha_email) >6){
                $response=array('status' => 0, 'msg' => '邮箱验证码不能为空或长度不符');
                return $response;
            }
        }
        return array('status' => 1, 'msg' => '合法','old_pwd'=>$pwd,'new_pwd'=>$new_pwd,'captcha_email'=>$captcha_email);
    }




}