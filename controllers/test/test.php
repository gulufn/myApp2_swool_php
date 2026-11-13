<?php
$kk=$ctx['user_id'];

$d=['status'=>1,'msg'=>'成功test','data'=>$kk];
\Core\HttpResponse::jsonResponse($response,$d);
return;