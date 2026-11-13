<?php
//HttpResponse.php
namespace Core;
class HttpResponse{
    /**
     * 响应json
     *
     * @param  \Swoole\Http\Response   $response     用swool提供响应对象
     * @param  array                   $data         要返回的json数据：['code'=>'1','msg'=>'ok'...]
     * @param  int                     $status       给浏览器看的http状态码
     */
    public static function jsonResponse(\Swoole\Http\Response $response,array $data, int $status = 200): void {
        $response->status($status);
        // 设置响应类型，json格式
        $response->header('Content-Type', 'application/json; charset=utf-8');
        // 响应的是json, 中文等非 ASCII 字符不转义，原样输出 | 输出带缩进换行的格式化 JSON，便于阅读,
        $response->end(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }


    /**
     * 响应图片
     *
     * @param  \Swoole\Http\Response   $response        用swool提供响应对象
     * @param  string                  $imageData       要返回的图片的二进制数据
     * @param  string                  $mime_type       给浏览器看的，图片类型
     */
    public static function imageResponse(\Swoole\Http\Response $response, string $imageData, string $mime_type = 'image/png'): void {
        $response->status(200);
        $response->header('Content-Type', $mime_type);
        $response->end($imageData);
    }
    
}