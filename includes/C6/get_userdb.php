<?php
/**
 * ファイル名：get_userdb.php
 * 版名：V1.1
 * 担当者：小泉 優
 * 日付：2025.06.28
 * 概要: C6 ユーザ情報管理部のM1.1 ユーザ情報取得処理を実装する。
 * ユーザIDに基づいてデータベースからユーザ情報を取得する機能を提供する。
 */

// データベース接続関数を読み込む
// 想定パス: プロジェクトルート/config/db_config.php
require_once __DIR__ . '/../../config/db_config.php';

/**
 * getUserByUserId (M1.1 ユーザ情報検索処理)
 * 担当者：小泉 優
 * 日付：2025.06.29
 * 機能概要: users テーブルから指定されたユーザIDに一致するユーザ情報を取得する。
 * 引数：string $user_id: 検索対象のユーザID
 * 返却値：array|false: ユーザ情報（user_id, password を含む連想配列）。ユーザが見つからない場合や、
 * データベースエラーが発生した場合は false を返す。
 * エラー処理:
 * - データベース接続エラー、SQL実行エラーが発生した場合、エラーログに記録し false を返す。
 */
function getUserByUserId(string $user_id) {
    try {
        $pdo = getDbConnection(); // データベース接続を取得
        
        // プリペアドステートメントを使用し、SQLインジェクション対策を行う
        $stmt = $pdo->prepare("SELECT user_id, password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]); // パラメータをバインド
        
        // 結果を1行取得
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user; // ユーザ情報があれば連想配列、なければfalse
    } catch (PDOException $e) {
        // データベース関連のエラーをログに記録
        error_log("データベースエラー (getUserByUserId): " . $e->getMessage());
        return false; // エラー発生時もfalseを返す
    } catch (Exception $e) {
        // その他のアプリケーションエラーをログに記録
        error_log("アプリケーションエラー (getUserByUserId): " . $e->getMessage());
        return false;
    }
}

/**
 * GetUserMain (M1 ユーザ情報検索主処理)
 * 担当者：小泉 優
 * 日付：2025.06.29
 * 機能概要: ユーザ情報取得のためのメイン処理。
 * 他のモジュールからユーザIDを受け取り、getUserByUserId関数を呼び出して結果を返す。
 * 引数：string $user_id: 取得したいユーザID
 * 返却値：array|false: getUserByUserId関数の結果をそのまま返す。
 */
function GetUserMain(string $user_id) {
    return getUserByUserId($user_id);
}

?>
