<?php
namespace Core;

class Utils
{
    //响应
    public static function jsonResponse(\Swoole\Http\Response $response, $data, int $status = 200): void {
        $response->status($status);
        $response->header('Content-Type', 'application/json; charset=utf-8');
        //响应的是json, 中文等非 ASCII 字符不转义，原样输出 | 输出带缩进换行的格式化 JSON，便于阅读
        $response->end(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
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
    //验证邮箱是否合法
    public static function judgeEmail(string $json){
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




}