<?php

namespace Core\Db;

class DbMusic
{
    //////////////////////////////////////////////////music表
    /**
     * 插入一条music数据
     *
     * @param  \PDO  $pdo
     * @param  array $data 数据类型为关联数组 字段名 => 值，如 ['artist' => '林俊杰', 'title' => '爱不会绝迹']
     * @return array $rdata
     */
    public static function insert_data_music( $pdo,array $data = []): array{
        // 允许插入的字段白名单
        $allowed = ['id','name', 'title', 'artist', 'status'];
        try {
            // 过滤掉不在白名单里的字段,array_flip()让数组变成关联数组，array_intersect_key(),取两个数组中的交集，以第一个为主
            $data = array_intersect_key($data, array_flip($allowed));
            if (empty($data)) {return ['code' => '0', 'msg' => '没有可插入的字段', 'data' => ''];}
            // ['name', 'title']，取$data里的key
            $cols = array_keys($data);                    
            // [':name', ':title']，把'key'变成':key'
            $placeholders = array_map(fn($c) => ":{$c}", $cols);  
            //开始拼接sql
            $sql = "INSERT INTO music (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            //预处理
            $statement = $pdo->prepare($sql);
            //循环给sql绑定值
            foreach ($data as $col => $val) {
                $statement->bindValue(":{$col}", $val);
            }
            //执行sql
            $statement->execute();
            $rdata=['sql'=>$sql,'params'=>$data];
            return ['code'=>'1','msg'=>'成功','data'=>$rdata];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode(),'params'=>$fields];
            return ['code'=>'0','msg'=>'sql执行失败','data'=>$rdata];
        }
    }

    /**
     * 查询通过id查询数据
     *
     * @param  \PDO   $pdo
     * @param  string $id  
     * @param  array  $fields ['id','name',......]
     * @return array  $rdata  
     */
    public static function select_data_by_id_music( $pdo,string $id, array $fields = []): array{
        // 白名单，防止 SQL 注入
        $allowed = ['id','name', 'title', 'artist', 'status'];
        try {
            //取两个数组的交集
            $fields = array_intersect($fields, $allowed);
            //如果传入的数组是空数组，则return
            if (empty($fields)) {return ['code'=>'0','msg'=>'没有合法字段','data'=>''];}
            //把$fields数组里的元素用逗号连接
            $cols = implode(',', $fields);
            $sql = "SELECT {$cols} FROM music WHERE id = :id";
            //预处理
            $statement = $pdo->prepare($sql);
            //绑定值
            $statement->bindValue(':id', $id);
            //执行sql
            $statement->execute();
            $row = $statement->fetch();
            return ['code'=>'1','msg'=>'成功','data'=>$row === false ? false : $row];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode(),'params'=>$fields];
            return ['code'=>'0','msg'=>'sql执行失败','data'=>$rdata];
        }
    }

    /**
     * 通过id更新数据
     *
     * @param  \PDO   $pdo
     * @param  string $id
     * @param  array  $data   数据类型为关联数组 字段名 => 值，如 ['id' => '6666', 'name' => 'xxx']
     * @return array  $rdata  
     */
    public static function update_data_music( $pdo,string $id,array $data = []): array{
        // 允许插入的字段白名单
        $allowed = ['id','name', 'title', 'artist', 'status'];
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
            $sql="UPDATE music SET ". implode(',', $set) . " WHERE id = :id";
            $statement = $pdo->prepare($sql);
            foreach ($data as $col => $val) {
                $statement->bindValue(":{$col}", $val);
            }
            $statement->bindParam(":id", $id);
            $statement->execute();
            $rdata=['sql'=>$sql,'params'=>$data,'stmt'=>$statement->rowCount()];
            return ['code'=>'1','msg'=>'成功','data'=>$rdata];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode(),'params'=>$fields];
            return ['code'=>'0','msg'=>'sql执行失败','data'=>$rdata];
        }
    }

    /**
     * 查询通过id删除数据
     *
     * @param  \PDO   $pdo
     * @param  string $id
     * @return array  $rdata
     */
    public static function delete_data_by_id_music( $pdo,string $id): array{
        try {
            $sql = "DELETE FROM music WHERE id = :id";
            //预处理
            $statement = $pdo->prepare($sql);
            //绑定值
            $statement->bindValue(':id', $id);
            //执行sql
            $statement->execute();
            $rows = $statement->rowCount();
            return ['code'=>'1','msg'=>'成功','data'=>$rows];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode()];
            return ['code'=>'0','msg'=>'sql执行失败','data'=>$rdata];
        }
    }

    /**
     * 查询所有 music 数据
     *
     * @param  \PDO   $pdo
     * @param  array  $fields  需要查询的字段 ['id','title','url',...]
     * @return array  $rdata
     */
    public static function select_all_music( $pdo,$fields = []): array {
        // 白名单，防止 SQL 注入
        $allowed = ['id','name', 'title', 'artist', 'status'];
        try {
            // 取两个数组的交集
            $fields = array_intersect($fields, $allowed);
            // 如果传入的数组是空数组，则 return
            if (empty($fields)) {return ['code' => '0', 'msg' => '没有合法字段', 'data' => ''];}

            // 把 $fields 数组里的元素用逗号连接
            $cols = implode(',', $fields);
            $sql = "SELECT {$cols} FROM music";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetchAll();

            $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rdata];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

    /**
     * 查询通过id查询数据
     *
     * @param  \PDO   $pdo
     * @param  string $title  
     * @param  array  $fields ['id','name',......]
     * @return array  $rdata  
     */
    public static function select_data_by_title_music( $pdo,string $title, array $fields = []): array{
        // 白名单，防止 SQL 注入
        $allowed = ['id','name', 'title', 'artist', 'status'];
        try {
            //取两个数组的交集
            $fields = array_intersect($fields, $allowed);
            //如果传入的数组是空数组，则return
            if (empty($fields)) {return ['code'=>'0','msg'=>'没有合法字段','data'=>''];}
            //把$fields数组里的元素用逗号连接
            $cols = implode(',', $fields);
            $sql = "SELECT {$cols} FROM music WHERE title LIKE :title LIMIT 15";
            //预处理
            $statement = $pdo->prepare($sql);
            //绑定值
            $statement->bindValue(':title', '%'.$title.'%');
            //执行sql
            $statement->execute();
            $rows = $statement->fetchAll();
            return ['code'=>'1','msg'=>'成功','data'=>$rows === false ? false : $rows];
        }catch (\PDOException $e) {
            $rdata=['sql'=>$sql,'error'=>$e->getMessage(),'sqlstate'=>$e->getCode(),'params'=>$fields];
            return ['code'=>'0','msg'=>'sql执行失败','data'=>$rdata];
        }
    }

}