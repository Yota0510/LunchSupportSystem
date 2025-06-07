<?php
/**
 * auth_functions.php
 *
 * 概要: ユーザー認証とセッション管理に関する関数を定義する。
 * 対応コンポーネント: C6 ユーザ情報管理部 (※内部設計書の記述はC2認証処理部とC6ユーザ情報管理部でやや重複があるため、実態に合わせる)
 * 対応モジュール: M2 ユーザ認証処理
 */

// データベース操作関数を読み込む
require_once __DIR__ . '/db_functions.php';

/**
 * authenticateUser (M2 ユーザ認証処理)
 *
 * 機能概要: データベースからユーザー情報を取得し、パスワードを検証する。
 * 入力:
 * - string $input_user_id: 入力されたユーザーID
 * - string $input_password: 入力されたパスワード
 * 出力:
 * - bool: 認証成功時はtrue、失敗時はfalse
 */
function authenticateUser(string $input_user_id, string $input_password): bool {
    // データベースからユーザー情報を取得
    $user = getUserByUserId($input_user_id);

    if ($user) {
        // ユーザーが見つかった場合
        // 【重要】パスワードの検証
        // 設計書ではVARCHAR(16)で生パスワードを想定しているようですが、
        // 実際のアプリケーションではパスワードはハッシュ化して保存し、
        // password_verify()関数などで検証します。
        // ここでは設計書通りに生パスワードでの比較を一旦行います。
        if ($input_password === $user['password']) {
            return true; // 認証成功
        }
    }
    return false; // 認証失敗 (ユーザーが見つからないか、パスワード不一致)
}

/**
 * isLoggedIn
 *
 * 機能概要: ユーザーがログインしているか確認する。
 * 入力: なし
 * 出力:
 * - bool: ログイン済みならtrue、そうでなければfalse
 */
function isLoggedIn(): bool {
    // セッションが開始されており、かつ 'user_id' がセッションにセットされていればログイン済み
    return isset($_SESSION['user_id']);
}

/**
 * logout
 *
 * 機能概要: ユーザーをログアウトさせる。
 * 入力: なし
 * 出力: なし
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
