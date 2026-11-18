<?php
require_once BASE_PATH . '/core/HttpResponse.php';

require_once BASE_PATH  . '/core/Crypto.php';
require_once BASE_PATH  . '/core/Utils.php';
require_once BASE_PATH  . '/core/Db/DbUser.php';
require_once BASE_PATH  . '/core/Db/DbMusic.php';
require_once BASE_PATH  . '/core/Db/DbMusicFavorites.php';


use Core\Crypto;
use Core\Utils;
use Core\Db\DbUser;
use Core\Db\DbMusic;
use Core\Db\DbMusicFavorites;

class MusicController
{
    // 获取音乐列表接口
    public function get_music_list($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            
            $youbiao=$post_json['youbiao'];
            if(!is_int($youbiao)){
                $data=array('status' => 0, 'msg' => '游标必须是int类型');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            if($youbiao < 0){
                $data=array('status' => 0, 'msg' => '游标必须是正整数');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            //从redis连接池里拿一个连接
            $redis = $redisPool->get();
            try {
                $redis->select(2);   //音乐在第二库
                $ids=json_decode($redis->get('music'), true);
            } finally {
                $redisPool->put($redis);   // 必须归还
            }
            
            if($youbiao >= count($ids)){
                $youbiao = random_int(0, count($ids)-15);
            }
            if($ids === null){
                $data = array('status' => 0,'msg' =>'redis中没有music的id数据');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            //获取上下文
            $ctx = \Swoole\Coroutine::getContext();
            $user_id = $ctx['user_id'] ?? '';

            $music_list_ids=array_slice($ids,$youbiao,15);
            $musics=[];
            for($i=0;$i<count($music_list_ids); $i++){
                $r = DbMusic::select_data_by_id_music($pdo,$music_list_ids[$i],['id','name','title','artist']);
                if($r['code']==0){
                    $data = array('status' => 0,'msg' => "数据库查询失败");
                    \Core\HttpResponse::jsonResponse($response,$data,500);
                    return;
                }
                $music_id = $r['data']['id'];
                $is_favorite = DbMusicFavorites::select_data_by_userid_musicid_userMusicFavorites($pdo,$user_id,$music_id);

                $musics[$i] = $r['data'];
                $musics[$i]['is_favorite'] = $is_favorite['data']['data'];
            }
            $key = Crypto::encrypt((string)time());

            $rdata = ['key'=>$key,'data'=>$musics];
            $data = array('status' => 1,'msg' => "成功",'data'=>$rdata);
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }else{
            $data = array('status' => 0,'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    // 音乐收藏接口
    public function toggle_favorites_music($pdo, $req,$response,$redisPool)
    {
        $ctx = \Swoole\Coroutine::getContext();
        $user_id = $ctx['user_id'];

        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data = array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $is_favorite=$post_json['is_favorite'];
            $music_id=$post_json['music_id'];

            if(!$is_favorite){
                $r = DbMusicFavorites::insert_data_userMusicFavorites($pdo,$user_id,$music_id);
                if($r['code']==0){
                    $data = array('status' => 0,'msg' => '收藏失败');
                    \Core\HttpResponse::jsonResponse($response,$data);
                    return;
                }
                $data = array('status' => 1,'msg' => '收藏成功');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $r = DbMusicFavorites::delete_data_userMusicFavorites($pdo,$user_id,$music_id);
            if($r['code']==0){
                $data = array('status' => 0,'msg' => '取消收藏失败');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $data = array('status' => 1,'msg' => '取消成功');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }else{
            $data = array('status' => 0,'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    // 音乐搜索接口
    public function search_music($pdo, $req,$response,$redisPool)
    {
        $ctx = \Swoole\Coroutine::getContext();
        $user_id = $ctx['user_id'];

        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data = array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }

            $title = $post_json['keyword'];

            $r = DbMusic::select_data_by_title_music($pdo,$title,['id','name','title','artist']);
            if($r['code']==0){
                $data = array('status' => 0,'message' => "数据库查询失败");
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            $c=[];
            $n=0;
            foreach ($r['data'] as $i) {
                $shoucang = DbMusicFavorites::select_data_by_userid_musicid_userMusicFavorites($pdo,$user_id,$i['id']);
                $i['is_favorite'] = $shoucang['data']['data'];
                $c[$n]=$i;
                $n++;
            }
            
            $data = array('status' => 1,'message' => "成功",'data'=>$c);
            \Core\HttpResponse::jsonResponse($response,$data);
            return;

        }else{
            $data = array('status' => 0, 'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }

    // 获取收藏的音乐列表
    public function get_music_list_collect($pdo, $req,$response,$redisPool)
    {
        if ($req['request_method'] === 'POST') {
            $json = $req['raw_content'];

            $post_json=json_decode($json,true);
            if (!is_array($post_json) || array_is_list($post_json)) {
                $data=array('status' => 0, 'msg' => 'JSON 格式错误');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            
            $youbiao=$post_json['create_at'];
            if(!is_string($youbiao)){
                $data=array('status' => 0, 'msg' => '游标必须是string类型');
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
            
            //获取上下文
            $ctx = \Swoole\Coroutine::getContext();
            $user_id = $ctx['user_id'] ?? '';

            if($youbiao == '0'){
                $r = DbMusicFavorites::select_data_by_userid_userMusicFavorites($pdo,$user_id);
                if($r['code']==0){
                    $data = array('status' => 0,'msg' => "数据查询失败");
                    \Core\HttpResponse::jsonResponse($response,$data,500);
                    return;
                }
                $music_collecp=[];
                $n=0;
                foreach ($r['data'] as $i){
                    $r1 = DbMusic::select_data_by_id_music($pdo,$i['music_id'],['id','name','title','artist']);

                    $r1['data']['created_at'] = $i['created_at'];
                    $r1['data']['collectd_id'] = $i['id'];
                    $r1['data']['is_favorite'] = true;
                    $music_collecp[$n]=$r1['data'];
                    $n++;
                }
                $key = Crypto::encrypt((string)time());
                $rdata = array('key'=>$key,'data'=>$music_collecp);
                $data = array('status' => 1,'msg' => "成功",'data'=>$rdata);
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }else{
                $r = DbMusicFavorites::select_data_by_userid_youbiao_userMusicFavorites($pdo,$user_id,$youbiao);
                if($r['code']==0){
                    $data = array('status' => 0,'msg' => "数据查询失败");
                    \Core\HttpResponse::jsonResponse($response,$data,500);
                    return;
                }

                $music_collecp=[];
                $n=0;
                foreach ($r['data']['data'] as $i){
                    $r1 = DbMusic::select_data_by_id_music($pdo,$i['music_id'],['id','name','title','artist']);

                    $r1['data']['created_at'] = $i['created_at'];
                    $r1['data']['collectd_id'] = $i['id'];
                    $r1['data']['is_favorite'] = true;
                    $music_collecp[$n]=$r1['data'];
                    $n++;
                }
                $key = Crypto::encrypt((string)time());
                $rdata = array('key'=>$key,'data'=>$music_collecp);
                $data = array('status' => 1,'msg' => "成功",'data'=>$rdata);
                \Core\HttpResponse::jsonResponse($response,$data);
                return;
            }
        }else{
            $data = array('status' => 0,'msg' => '请使用post请求');
            \Core\HttpResponse::jsonResponse($response,$data);
            return;
        }
    }







}