<?php
require_once BASE_PATH . '/core/HttpResponse.php';

require_once BASE_PATH  . '/core/Crypto.php';
require_once BASE_PATH  . '/core/Utils.php';
require_once BASE_PATH  . '/core/Db/DbUser.php';
require_once BASE_PATH  . '/core/Db/DbChat.php';



use Core\Crypto;
use Core\Utils;
use Core\Db\DbUser;
use Core\Db\DbChat;


class AiChatController
{
    // 获取音乐列表接口
    public function get_chat_list($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            
            $youbiao = $post_json['youbiao'] ?? null;
            if($youbiao === null || !is_string($youbiao) || trim($youbiao) === '' || mb_strlen($youbiao) > 1000 || mb_strlen($youbiao) < 1){
                $response=array('status' => 0, 'msg' => '游标不能为空，游标是"2026-11-05 03:05:38"类似的字符串');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $ctx = \Swoole\Coroutine::getContext();
            $user_id = $ctx['user_id'];

            if($youbiao == '0'){
                $r = DbChat::select_data_by_userid_chat($pdo,$user_id);
                if($r['code']== 0){
                    $data = array('status' => 0,'msg' => "数据查询失败");
                    \Core\HttpResponse::jsonResponse($response,$data,500);
                    return;
                }
                $data = array('status' => 1,'msg' => "成功",'data'=>$r['data']);
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }else{
                $r = DbChat::select_data_by_userid_youbiao_chat($pdo,$user_id,$youbiao);
                if($r['code']==0){
                    $data = array('status' => 0,'msg' => "数据查询失败");
                    \Core\HttpResponse::jsonResponse($response,$data);
                    return;
                }
                $data = array('status' => 1,'msg' => "成功",'data'=>$r['data'],'t'=>$youbiao);
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
        }else{
            $data = array('status' => 0, 'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    // 提问
    public function create_chat_task($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $content = $post_json['content'] ?? null;
            if($content === null || !is_string($content) || trim($content) === '' || mb_strlen($content) > 1000 || mb_strlen($content) < 1){
                $data = array('status' => 0, 'msg' => '内容不能为空或长度超过1000');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            // Hy-MT2-1.8B 支持语种：中文全称   # 民族语言 / 方言
            $langList = array("中文","英语","法语","葡萄牙语","西班牙语","日语","土耳其语","俄语","阿拉伯语","韩语","泰语","意大利语","德语","越南语","马来语","印尼语","菲律宾语","印地语","繁体中文","波兰语","捷克语","荷兰语","高棉语","缅甸语","波斯语","古吉拉特语","乌尔都语","泰卢固语","马拉地语","希伯来语","孟加拉语","泰米尔语","乌克兰语","藏语","哈萨克语","蒙古语","维吾尔语","粤语");
            $target_lang = $post_json['target_lang'] ?? '英语';
            if (!in_array($target_lang, $langList)) {
                $data = array('status' => 0, 'msg' => '语言不在范围内,请选择列表中的语种','langList'=>$langList);
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $ctx = \Swoole\Coroutine::getContext();
            $user_id = $ctx['user_id'];

            $r = DbChat::insert_data_chat($pdo,['user_id'=>$user_id,'content'=>$content,'target_lang'=>$target_lang,'role'=>1]);
            if($r['code'] ==0 ){
                $data=array('status' => 0,'msg' => '插入失败');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $r = DbChat::select_one_data_by_userid_chat($pdo,$user_id);
            if($r['code'] == 0){
                $data=array('status' => 0,'msg' => '读取失败');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $data = array('status' => 1,'msg' => '成功','data'=>$r['data']);
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }else{
            $data=array('status' => 0, 'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    public function get_answer($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $link = $post_json['id'] ?? null;
            if($link === null || !is_int($link)){
                $data = array('status' => 0, 'msg' => 'chat_id不可为空');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $ctx = \Swoole\Coroutine::getContext();
            $user_id = $ctx['user_id'];
            $r = DbChat::select_data_by_userid_link_chat($pdo,$user_id,$link);
            if($r['code'] == 0){
                $data = array('status' => 0, 'msg' => '失败');
                \Core\HttpResponse::jsonResponse($response,$data,500);
                return;
            }
            if(!$r['data']){
                $data = array('status' => 0,'msg' => '没干完','t'=>$r);
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $data = array('status' => 1,'msg' => '成功','data'=>$r['data']);
            \Core\HttpResponse::jsonResponse($response,$data);
            return;

        }else{
            $data=array('status' => 0, 'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    public function get_task($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $key = $post_json['key'] ?? '';
            if($key != 'ttxs'){
                $data=array('status' => 0, 'msg' => '身份错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $r = DbChat::select_data_by_role_chat($pdo);
            if($r['code'] ==0 ){
                $data=array('status' => 0, 'msg' => '获取失败');
                \Core\HttpResponse::jsonResponse($response,$data,500);
                return;
            }
            
            $data=[];
            $n=0;
            foreach ($r['data']['data'] as $i) {
                $data[$n]['id']=$i['id'];
                $data[$n]['user_id']=$i['user_id'];
                $data[$n]['content']=$i['content'];
                $data[$n]['target_lang']=$i['target_lang'];
                $n++;
            }
            
            $rdata=array('status' => 1,'msg' => '成功','data'=>$data);
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;

        }else{
            $data=array('status' => 0, 'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    public function submit_task($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $key = $post_json['key'] ?? '';
            if($key != 'ttxs'){
                $data=array('status' => 0, 'msg' => '身份错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $id = $post_json['id'];
            $user_id = $post_json['user_id'];
            $content = $post_json['content'];
            $role = 2;
            $status = 1;
            $r = DbChat::update_data_chat($pdo,$id,['status'=>$status]);
            $rr = DbChat::insert_data_chat($pdo,['user_id'=>$user_id,'content'=>$content,'role'=>$role,'status'=>$status,'link'=>$id]);
            
            
            
            $rdata = array('status' => 1,'msg' => '成功','t'=>$post_json,'r'=>$r,'rr'=>$rr);
           
            \Core\HttpResponse::jsonResponse($response,$rdata);
            return;

        }else{
            $data=array('status' => 0, 'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }


}