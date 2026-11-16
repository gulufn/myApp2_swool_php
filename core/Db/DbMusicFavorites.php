<?php

namespace Core\Db;

class DbMusicFavorites
{
    ///////////////////////////////////////////////////////////user_music_favorites表

    /**
     * 查询用户收藏的音乐，按时间倒序，取前x个
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @return array  $rdata
     */
    public static function select_data_by_userid_userMusicFavorites( $pdo,$user_id): array {
        try {
            $sql = "SELECT id,music_id,created_at FROM user_music_favorites WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 15";
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
     * 通过userid和游标 查询用户收藏的音乐，按时间倒序，取前x个
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @param  string $youbiao
     * @return array  $rdata
     */
    public static function select_data_by_userid_youbiao_userMusicFavorites( $pdo,$user_id,$youbiao): array {
        try {
            $sql = "SELECT id,music_id,created_at FROM user_music_favorites WHERE user_id = :user_id  AND created_at < :youbiao ORDER BY created_at DESC LIMIT 10";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            $statement->bindValue(':youbiao', $youbiao, \PDO::PARAM_STR);
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
     * 判断用户是否收藏了某个音乐
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @param  int    $music_id
     * @return array  $rdata
     */
    public static function select_data_by_userid_musicid_userMusicFavorites( $pdo,$user_id,$music_id): array {
        try {
            $sql = "SELECT 1 FROM user_music_favorites WHERE user_id = :user_id AND music_id = :music_id LIMIT 1";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            $statement->bindValue(':music_id', $music_id, \PDO::PARAM_INT);
            // 执行 sql
            $statement->execute();
            $rows = $statement->fetch() !== false;

            $rdata = ['sql' => $sql, 'data' =>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rdata];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

    /**
     * 判断用户是否收藏了某个音乐
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @param  int    $music_id
     * @return array  $rdata
     */
    public static function insert_data_userMusicFavorites( $pdo,$user_id,$music_id): array {
        try {
            $sql = "insert into user_music_favorites(user_id, music_id)VALUES(:user_id, :music_id)";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            $statement->bindValue(':music_id', $music_id, \PDO::PARAM_INT);
            // 执行 sql
            $statement->execute();

            $rdata = ['sql' => $sql];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rdata];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }

    /**
     * 判断用户是否收藏了某个音乐
     *
     * @param  \PDO   $pdo
     * @param  int    $user_id
     * @param  int    $music_id
     * @return array  $rdata
     */
    public static function delete_data_userMusicFavorites( $pdo,$user_id,$music_id): array {
        try {
            // $sql = "insert into user_music_favorites(user_id, music_id)VALUES(:user_id, :music_id)";
            $sql = "DELETE FROM user_music_favorites WHERE user_id = :user_id AND music_id = :music_id";
            // 预处理
            $statement = $pdo->prepare($sql);
            // 绑定值
            $statement->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
            $statement->bindValue(':music_id', $music_id, \PDO::PARAM_INT);
            // 执行 sql
            $statement->execute();
            $rows = $statement->rowCount();
            $rdata = ['sql' => $sql,'$rows'=>$rows];
            return ['code' => '1', 'msg' => 'sql执行成功', 'data' => $rdata];
        } catch (\PDOException $e) {
            $rdata = ['sql' => $sql, 'error' => $e->getMessage(), 'sqlstate' => $e->getCode()];
            return ['code' => '0', 'msg' => 'sql执行失败', 'data' => $rdata];
        }
    }
}