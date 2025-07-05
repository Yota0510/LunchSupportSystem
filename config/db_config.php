<?php
// /var/www/lunch_support_system/config/db_config.php

// データベースファイルへのパス
// __DIR__ はこのPHPファイルがあるディレクトリを示します。
// そこから親ディレクトリに移り、dataディレクトリ、OrderManage.dbを指定します。
define('DB_PATH', __DIR__ . '/../data/OrderManage.db');

/**
 * データベースに接続し、PDOオブジェクトを返す関数
 * @return PDO データベース接続オブジェクト
 * @throws PDOException データベース接続に失敗した場合
 */
function getDbConnection() {
    try {
        // SQLiteへの接続
        // DSN (Data Source Name) は 'sqlite:' の後にデータベースファイルのパスを指定
        $pdo = new PDO('sqlite:' . DB_PATH);
        
        // エラーモードを例外に設定
        // これにより、SQLエラーが発生した場合にPDOExceptionがスローされます。
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 結果セットのカラム名をPHPでアクセスできるように設定 (FETCH_ASSOCは連想配列)
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    } catch (PDOException $e) {
        // 接続失敗時のエラーメッセージ
        // 本番環境では詳細なエラーメッセージは表示せず、ログに記録すべきです。
        error_log("データベース接続エラー: " . $e->getMessage());
        throw new Exception("データベース接続に失敗しました。管理者にご連絡ください。");
    }
}

?>
