<?php

require __DIR__ . '/../config/mysql_init.php';
require __DIR__  . '/../core/Db/DbMusic.php';


use Core\Db\DbMusic;


$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->select(2);


//################################把音乐id打乱顺序存入redis
$r = DbMusic::select_all_music($pdo,['id']);
$ids = array_column($r['data']['data'], 'id');
shuffle($ids);
$redis->del('music');
$redis->setex('music', 86400, json_encode($ids));    //每天更新音乐列表
//##########################################################


