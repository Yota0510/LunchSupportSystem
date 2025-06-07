<?php
// includes/db_functions.php (C6 ユーザ情報管理部 M1 ユーザ情報管理主処理, C8 診断店舗情報管理部 M1 診断店舗情報管理主処理)

// データベース接続関数を読み込む
require_once __DIR__ . '/../config/db_config.php';

/**
 * usersテーブルからユーザー情報を取得する
 *
 * @param string $user_id 検索するユーザーID
 * @return array|false ユーザー情報（連想配列）または見つからなかった場合はfalse
 */
function getUserByUserId(string $user_id) {
    try {
        $pdo = getDbConnection(); // データベース接続を取得
        
        // プリペアドステートメントを使用し、SQLインジェクション対策を行う
        $stmt = $pdo->prepare("SELECT user_id, password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]); // パラメータをバインド
        
        // 結果を1行取得
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user; // ユーザー情報があれば連想配列、なければfalse
    } catch (PDOException $e) {
        error_log("データベースエラー (getUserByUserId): " . $e->getMessage());
        return false; // エラー発生時もfalseを返す
    } catch (Exception $e) {
        error_log("アプリケーションエラー (getUserByUserId): " . $e->getMessage());
        return false;
    }
}

/**
 * usersテーブルに新しいユーザーを登録する
 *
 * @param string $user_id
 * @param string $password (ハッシュ化済みを推奨)
 * @param int $serial_num 連番 (設計書 F1 ユーザ情報 に基づく)
 * @param string $favorite_store_id お気に入り店舗ID (設計書 F1 ユーザ情報 に基づく)
 * @return bool 登録成功時はtrue、失敗時はfalse
 */
function registerUser(string $user_id, string $password, int $serial_num, string $favorite_store_id = ''): bool {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO users (user_id, serial_num, password, favorite_store_id) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$user_id, $serial_num, $password, $favorite_store_id]);
    } catch (PDOException $e) {
        error_log("データベースエラー (registerUser): " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("アプリケーションエラー (registerUser): " . $e->getMessage());
        return false;
    }
}


// 必要に応じて、他のDB操作関数 (店舗情報取得、更新など) をここに追加していく
// 例:
// function getStoreInfoById(int $store_id) { /* ... */ }
// function saveFavoriteStore(string $user_id, string $store_id) { /* ... */ }
// ...

?>
