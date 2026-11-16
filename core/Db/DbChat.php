<?php

namespace Core\Db;

class DbChat
{
    ////////////////////////////////////////////////////chat表
    /**
     * 插入一条聊天记录数据
     *
     * @param  \PDO  $pdo
     * @param  array $data 数据类型为关联数组 字段名 => 值，如 ['user_id' => '1', 'content' => 'xxx']
     * @return array $rdata
     */
    public static function insert_data_chat( $pdo,array $data = []): array{
        // 允许插入的字段白名单
        $allowed = ['id', 'user_id', 'content', 'role', 'target_lang', 'created_at', 'status','link'];
        try {
            // 过滤掉不在白名单里的字段,array_flip()让数组变成关联数组，array_intersect_key(),取两个数组中的交集，以第一个为主
            $data = array_intersect_key($data, array_flip($allowed));
            if (empty($data)) {return ['code' => '0', 'msg' => '没有可插入的字段', 'data' => ''];}
            // ['email', 'pwd']，取$data里的key
            $cols = array_keys($data);                    
            // [':email', ':pwd']，把'key'变成':key'
            $placeholders = array_map(fn($c) => ":{$c}", $cols);  
            //开始拼接sql
            $sql = "INSERT INTO chat (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            //预处理
            $statement = $pdo->prepare($sql);
            //循环给sql绑定值
            foreach ($data as $col => $val) {
                $statement->bindValue(":{$col}", $val);
            }
            //执行sql
            $statement->execute();
            $rdata=['sql'=>$sql,'params'=>$data];
            return ['code'=>1,'msg'=>'成功','data'=>$rdata];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode()];
            return ['code'=>0,'msg'=>'sql执行失败','data'=>$rdata];
        }
    }
    /**
     * 查询用户翻译记录，按时间倒序，取第1个
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @return array  $rdata
     */
    public static function select_one_data_by_userid_chat( $pdo,$user_id): array {
        try {
            $sql = "SELECT id,content,`role`,target_lang,created_at,`status` FROM chat WHERE user_id = :user_id AND `role` = 1 AND `status` != 2 ORDER BY created_at DESC LIMIT 1";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetchall();

            // $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rows];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

    /**
     * 查询用户翻译结果
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @param  int    $link               
     * @return array  $rdata
     */
    public static function select_data_by_userid_link_chat( $pdo,$user_id,$link): array {
        try {
            $sql = "SELECT id,content,`role`,target_lang,created_at,`status` FROM chat WHERE user_id = :user_id AND `role` = 2 AND `status` != 2 AND link = :link";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            $statement->bindValue(':link', $link, \PDO::PARAM_INT);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetchall();

            // $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rows];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

     /**
     * 查询任务列表，按时间倒序，取前x个
     *
     * @param  \PDO   $pdo
     * @return array  $rdata
     */
    public static function select_data_by_role_chat( $pdo): array {
        try {
            $sql = "SELECT * FROM chat WHERE role = 1  AND status = 0 ORDER BY created_at ASC LIMIT 1";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetchall();

            $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rdata];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

    /**
     * 查询用户翻译记录，按时间倒序，取前x个
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @return array  $rdata
     */
    public static function select_data_by_userid_chat( $pdo,$user_id): array {
        try {
            $sql = "SELECT id,content,`role`,target_lang,created_at,`status` FROM chat WHERE user_id = :user_id AND `status` != 2 ORDER BY created_at DESC LIMIT 10";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetchall();

            // $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rows];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }
    /**
     * 通过userid和游标 查询用户翻译记录，按时间倒序，取前x个
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @param  string $youbiao
     * @return array  $rdata
     */
    public static function select_data_by_userid_youbiao_chat( $pdo,$user_id,$youbiao): array {
        try {
            $sql = "SELECT id,content,`role`,target_lang,created_at,`status` FROM chat WHERE user_id = :user_id  AND created_at < :youbiao ORDER BY created_at DESC LIMIT 5";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            $statement->bindValue(':youbiao', $youbiao, \PDO::PARAM_STR);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetchall();

            // $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rows];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

    /**
     * 通过id更新数据
     *
     * @param  \PDO   $pdo
     * @param  int    $id      如2、3、8
     * @param  int    $status   数据类型为关联数组 字段名 => 值，如 ['status' => 1]
     * @return array  $rdata  
     */
    public static function update_data_chat( $pdo,int $id,array $data = []): array{
        // 允许插入的字段白名单
        $allowed = ['id', 'user_id', 'content', 'role', 'target_lang', 'created_at', 'status'];
        try {
            // 过滤掉不在白名单里的字段
            $data = array_intersect_key($data, array_flip($allowed));
            if (empty($data)) {return ['code' => '0', 'msg' => '没有可更新的字段', 'data' => ''];}
            $set = [];
            $params = [];
            foreach ($data as $col => $val) {
                $ph = ":{$col}";
                $set[] = "{$col} = {$ph}";
                $params[$ph] = $val;
            }
            $sql="UPDATE chat SET ". implode(',', $set) . " WHERE id = :id";
            $statement = $pdo->prepare($sql);
            foreach ($data as $col => $val) {
                $statement->bindValue(":{$col}", $val);
            }
            $statement->bindParam(":id", $id);
            $statement->execute();
            $rdata=['sql'=>$sql,'params'=>$data,'stmt'=>$statement->rowCount()];
            return ['code'=>1,'msg'=>'成功','data'=>$rdata];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode()];
            return ['code'=>0,'msg'=>'sql执行失败','data'=>$rdata];
        }
    }

}