<?php
/**
 * ファイル名；user_service.php
 * 版名：V1.0
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 概要: ユーザー関連のビジネスロジックをカプセル化するサービス層。
 *       C1 (UI処理部) からの要求を受け、C6 (ユーザ情報管理部) のDB操作を仲介する。
 * 対応コンポーネント: C2 認証処理部
 * 対応モジュール: M2.1 ユーザID生成処理, M2 新規ユーザ登録サービス処理
 */


// C6のデータベース操作関数を読み込む
require_once __DIR__ . '/../C6/register_userdb.php';
require_once __DIR__ . '/../C6/get_userdb.php';

/**
 * M2.1 ユーザーID生成処理(GenerateUniqueUserId)
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 機能概要: 0から9999の範囲で、既存と重複しないユニークなユーザーIDを生成する。
 * 引数：なし
 * 返却値: string: 生成されたユニークなユーザーID
 * エラー処理:
 * - 適切なIDが生成できない場合のエラーハンドリング
 */
function GenerateUniqueUserId(): string {
    $max_attempts = 1000;
    for ($i = 0; $i < $max_attempts; $i++) {
        $new_user_id = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        // getUserByUserId が false を返す場合はユーザーが存在しない、つまりユニーク
        // includes/C6/userdb_manager.php の getUserByUserId 関数を呼び出す
        if (GetUserMain($new_user_id) === false) {
            return $new_user_id;
        }
    }

    error_log("Failed to generate a unique user ID after " . $max_attempts . " attempts.");
    return "";
}

/**
 * M2 新規ユーザー登録サービス処理 (RegisterNewUser)
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 機能概要: 新しいユーザー情報をシステムに登録する。
 * ユニークなユーザーIDを生成し、データベースに登録を試みる。
 * 引数:
 * - string $password: 登録するパスワード
 * 返却値: array: ['status' => "OK" or "NG", 'user_id' => string|null]
 * エラー処理:
 * - ID生成失敗時やDB登録失敗時にNGステータスを返す
 */
function RegisterNewUser(string $password): array {
    $new_user_id = GenerateUniqueUserId();
    if ($new_user_id === "") {
        return ['status' => "NG", 'user_id' => null]; // ID生成に失敗
    }

    $serial_num = 1; // 仮の初期値。DBのAUTO_INCREMENTを使うなら不要
    $favorite_store_id = ''; // 仮の初期値

    // includes/C6/userdb_manager.php の RegisterUserMain 関数を呼び出す
    if (RegisterUserMain($new_user_id, $password, $serial_num, $favorite_store_id)) {
        return ['status' => "OK", 'user_id' => $new_user_id]; // 成功時に生成されたIDを返す
    } else {
        return ['status' => "NG", 'user_id' => null]; // DB登録失敗
    }
}
