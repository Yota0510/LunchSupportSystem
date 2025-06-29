<?php
/**
 * ファイル名：register_userdb.php
 * 版名：V1.0
 * 担当者：鈴木 馨
 * 日付：2025.06.29
 * 概要: C6 ユーザー情報管理部のM1.2 ユーザー情報登録処理を実装する。
 * 新しいユーザーをデータベースに登録する機能を提供する。
 */

// データベース接続関数を読み込む
// 想定パス: プロジェクトルート/config/db_config.php
require_once __DIR__ . '/../../config/db_config.php';

/**
 * registerUser (M3.1 ユーザー情報登録処理)
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 機能概要: users テーブルに新しいユーザー情報を登録する。
 * 引数:
 * - string $user_id: 新規登録するユーザーのユニークなユーザーID
 * - string $password: 登録するパスワード（**ハッシュ化後の文字列**）
 * - int $serial_num: ユーザーの連番 (設計書 F1 ユーザー情報に基づく。DBでAUTO_INCREMENT推奨)
 * - string $favorite_store_id: お気に入り店舗ID (設計書 F1 ユーザー情報に基づく。初期値は空文字列)
 * 返却値:
 * - bool: 登録成功時は true、失敗時は false を返す。
 *
 * エラー処理:
 * - データベース接続エラー、SQL実行エラーが発生した場合、エラーログに記録し false を返す。
 *
 */
function registerUser(string $user_id, string $password, int $serial_num, string $favorite_store_id = ''): bool {
    try {
        $pdo = getDbConnection();
        
        // serial_num をプログラム側で設定する場合のINSERT文
        $stmt = $pdo->prepare(
            "INSERT INTO users (user_id, serial_num, password, favorite_store_id) VALUES (?, ?, ?, ?)"
        );
        
        return $stmt->execute([$user_id, $serial_num, $password, $favorite_store_id]);
    } catch (PDOException $e) {
        // データベース関連のエラーをログに記録
        error_log("データベースエラー (registerUser): " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        // その他のアプリケーションエラーをログに記録
        error_log("アプリケーションエラー (registerUser): " . $e->getMessage());
        return false;
    }
}

/**
 * RegisterUserMain (M3 ユーザー情報登録主処理)
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 機能概要: 新しいユーザー情報をデータベースに登録するためのメイン処理。
 * 他のモジュールから必要なデータを受け取り、registerUser関数を呼び出して結果を返す。
 *
 * 引数:
 * - string $user_id: 新規登録するユーザID
 * - string $password: 登録するパスワード
 * - int $serial_num: ユーザの連番
 * - string $favorite_store_id (オプション, デフォルト: ''): お気に入り店舗ID
 * 返却値:
 * - bool: registerUser関数の結果をそのまま返す。
 */
function RegisterUserMain(string $user_id, string $password, int $serial_num, string $favorite_store_id = ''): bool {
    return registerUser($user_id, $password, $serial_num, $favorite_store_id);
}

?>
