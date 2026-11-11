<?php
// header('Content-Type: application/json');
header("Access-Control-Allow-Origin: * ");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
//拦截器
require_once BASE_PATH . '/config/mysql_init.php';
require_once BASE_PATH  . '/core/Db.php';
use Core\Db;

class AuthMiddleware {
    public function handle(): array {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit(0);
        }
       
        $token = $this->getToken();
        if($token === null){
            $this->reject(400, 'token不可为空');
        }
        if ($token === '') {
            $this->reject(402, 'token不可以为空字符串');
        }
        return $payload;
    }

    private function getToken(): ?string {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s(\S+)/i', $header, $m)) {
            return $m[1];
        }
        return $_GET['token'] ?? $_POST['token'] ?? null;
    }

    private function verifyToken(string $token): ?array {
        if($token !== 'abcd'){
             $this->reject(403, 'Token错误');
        }
        return $r['data'];
    }

    private function reject(int $code, string $msg): void {
        header('Content-Type: application/json');
        $response = array('status' => $code,'msg' =>$msg);
        echo json_encode($response);    
        exit();
    }
}