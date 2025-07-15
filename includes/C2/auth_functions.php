<?php
/**
 * ファイル名：auth_functions.php
 * 版名：V1.0
 * 担当者：小泉 優
 * 日付：2025.06.30
 * 概要: ユーザー認証とセッション管理に関する関数を定義する。
 * 対応コンポーネント: C6 ユーザ情報管理部 (※内部設計書の記述はC2認証処理部とC6ユーザ情報管理部でやや重複があるため、実態に合わせる)
 * 対応モジュール: M2 ユーザ認証処理
 */

// データベース操作関数を読み込む
require_once __DIR__ . '/../C6/get_userdb.php';

/**
 * M1 認証主処理 (AuthMain)
 * 担当者: 小泉 優
 * 日付：2025.07.01
 * 機能概要：データベースからユーザ情報を取得し，パスワードを認証した結果を返す．
 * 引数：string $input_user_id
 * 引数：string $input_password
 * 返却値：bool "true"または"false"
 */
function AuthMain(string $input_user_id, string $input_password): bool {
    // データベースからユーザ情報を取得
    $user = GetUserMain($input_user_id);

    if ($user && VerifyPassword($input_password, $user['password'])) {
        return true;
    }

    return false;
}

/**
 * VerifyPassword (M1.1 認証処理)
 * 担当者：小泉 優
 * 日付：2025.07.01
 * 機能概要：パスワードを検証する。
 * 引数：string $input_password
 * 引数：string $stored_password
 * 返却値：bool "true"または"false"
 */
function VerifyPassword(string $input_password, string $stored_password): bool {
    return $input_password === $stored_password;
}


/**
 * isLoggedIn
 * 機能概要: ユーザーがログインしているか確認する。
 * 引数：なし
 * 返却値：bool ログイン済みならtrue、そうでなければfalse
 */
function isLoggedIn(): bool {
    // セッションが開始されており、かつ 'user_id' がセッションにセットされていればログイン済み
    return isset($_SESSION['user_id']);
}

/**
 * logout
 * 機能概要: ユーザーをログアウトさせる。
 * 引数：なし
 * 返却値：なし
 */
function logout(): void {
    $_SESSION = array(); // セッション変数を全て解除

    // セッションクッキーを削除
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); // セッションを破棄
}
