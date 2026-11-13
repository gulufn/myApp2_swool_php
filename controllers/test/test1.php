<?php

$r=[];

try {
    $sql = "SELECT * FROM users";
    //预处理
    $statement = $pdo->prepare($sql);
    //执行sql
    $statement->execute();
    $row = $statement->fetchall();
    $r = ['code'=>1,'msg'=>'成功','data'=>$row === false ? false : $row];
}catch (\PDOException $e) {
    $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode(),'params'=>$fields];
    $r= ['code'=>0,'msg'=>'sql执行失败','data'=>$rdata];
}

$d=['status'=>1,'msg'=>'这是test1','data'=>$r];
\Core\HttpResponse::jsonResponse($response,$d);
return;