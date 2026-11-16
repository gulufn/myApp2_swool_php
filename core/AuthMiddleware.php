<?php
//拦截器
require_once BASE_PATH  . '/core/Db/DbUser.php';
use Core\Db\DbUser;
require_once BASE_PATH . '/core/HttpResponse.php';
use Core\HttpResponse;      //语法糖，起短名

class AuthMiddleware {
    public function handle($pdo): array {
        //获取需要的对象和数据
        $db = new DbUser;
        $ctx = \Swoole\Coroutine::getContext();
        $response = $ctx['response'];
        $req=$ctx['req'];

        //获取token
        $token = $this->getToken($req);
        if($token === ''){
            $rdata=['status'=>0,'msg'=>'没有token'];
            HttpResponse::jsonResponse($response,$rdata,401);
            return [];
        }
        $payload = $this->verifyToken($token,$pdo,$db,$response);
        if (!$payload) {
            $rdata=['status'=>0,'msg'=>'token无效'];
            HttpResponse::jsonResponse($response,$rdata,401);
            return [];
        }
        
        return $payload;
    }
    //从$req里获取token，并返回
    private function getToken(array $req): string {
        //获取请求头中的authorization，（由客户端设置，一般放token）
        $header=$req['authorization'];
        //用正则提取token并返回，没匹配到就是false
        if (preg_match('/Bearer\s(\S+)/i', $header, $m)) {
            return $m[1];
        }
        return $req['get']['token'] ?? $req['post']['token'] ?? '';
    }
    //验证token
    private function verifyToken($token,$pdo,$db,$response): array {
        //开始查询
        $r = $db->select_data_by_token_users($pdo, $token, ['id','token_update_time','is_activated']);
        if($r['code'] == 0){
            $rdata=['status'=>0,'msg'=>'sql执行失败'];
            HttpResponse::jsonResponse($response,$rdata,501);
            return [];
        }
        // 查不到 / 返回结构不对 / data 为空，都视为无效
        if (!$r || !isset($r['data']) || empty($r['data'])) {
            $rdata=['status'=>0,'msg'=>'token查询失败'];
            HttpResponse::jsonResponse($response,$rdata,401);
            return [];
        }
        //计算时间差
        $seconds = time() - strtotime($r['data']['token_update_time']);
        if($seconds > 60*60*24){
            $rdata=['status'=>0,'msg'=>'token已过期，请重新登录'];
            HttpResponse::jsonResponse($response,$rdata,401);
            return [];
        }
        if($r['data']['is_activated'] == 0){
            $rdata=['status'=>0,'msg'=>'账号已被冻结，请解冻'];
            HttpResponse::jsonResponse($response,$rdata,403);
            return [];
        }
        return $r['data'];
    }

  
}