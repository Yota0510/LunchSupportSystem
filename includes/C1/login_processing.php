<?php
/**
 * ファイル名：login_processing.php
 * 版名：V1.0
 * 担当者：//
 * 日付：2025.06.30
 * 概要: C1 UI処理部 M1 ログイン主処理のサブモジュールを実装する。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M1.1 入力確認処理, M1.2 認証要求処理, M1.3 画面遷移処理
 */

// 認証関連関数を読み込む (M1.2 認証要求処理で必要)
require_once __DIR__ . '/../C2/auth_functions.php';

/**
 * CheckLoginInput (M1.1 入力確認処理)
 * 担当者：小泉 優
 * 機能概要: ユーザIDとパスワードの入力の確認を行う。
 * 引数：
 * - string $user_id: ユーザID (W1ログイン画面より)
 * - string $password: パスワード (W1ログイン画面より)
 * 返却値：
 * - bool $is_valid: 入力が正しければtrue、そうでなければfalse
 * - string $error_msg: エラーメッセージ（入力エラー時）
 * エラー処理:
 * - 入力文字列が空欄の場合、入力エラー（E3，E4）としてエラーメッセージを返す。
 */
function CheckLoginInput(string $user_id, string $password, ?string &$error_msg = null): bool {
    $is_valid = true;
    $error_msg = '';

    if (empty($user_id)) {
        $error_msg .= 'ユーザーIDが入力されていません。(E3) <br>';
        $is_valid = false;
    }
    if (empty($password)) {
        $error_msg .= 'パスワードが入力されていません。(E4) <br>';
        $is_valid = false;
    }

    if (strlen($user_id) > 5) {
        $error_msg .= 'ユーザーIDが長すぎます。(E1) <br>';
        $is_valid = false;
    }

    if (strlen($password) > 16) {
        $error_msg .= 'パスワードが長すぎます。(E2) <br>';
        $is_valid = false;
    }

    return $is_valid;
}

/**
 * SendAuthenticationRequest (M1.2 認証要求処理)
 * 担当者：樋口 智也
 * 機能概要: ユーザIDとパスワードをC2認証処理部に送り、認証結果を取得する。
 * 引数：
 * - string $user_id: ユーザID (M1 ログイン主処理より)
 * - string $password: パスワード (M1 ログイン主処理より)
 * 返却値：
 * - string $exist: 認証成功なら"OK"、失敗なら"NG"
 * エラー処理:
 * - 通信失敗の場合、通信エラーが起きたことを報告し、W1ログイン画面に戻る。(E5)
 */
function SendAuthenticationRequest(string $user_id, string $password): string {
    if (AuthMain($user_id, $password)) {
        return "OK"; // 認証成功
    } else {
        return "NG"; // 認証失敗
    }
}

/**
 * DisplayLoginResult (M1.3 画面遷移処理)
 * 担当者：田口 陽太
 * 機能概要: 認証結果に基づいて、スタート画面（W2）またはログイン画面（W1）に遷移し、必要なメッセージを表示する。
 * 引数：
 * - string $exist: "OK" または "NG" (M1 ログイン主処理より)
 * - string $error_message_for_display: 表示するエラーメッセージ (認証失敗時)
 * 返却値：
 * - HTML: "OK"の場合W2スタート画面へリダイレクト。
 * - HTML: "NG"の場合W1ログイン画面を再表示（エラーメッセージ付き）。
 * エラー処理:
 * - 通信失敗の場合、通信エラーが起きたことを報告し、W1ログイン画面に戻る。(E5)
 * (この関数が呼ばれる時点で認証結果が得られているため、ここでは直接通信エラーは扱わない。
 * SendAuthenticationRequestでエラーが"NG"として返ってくることで対応する。)
 */
function DisplayLoginResult(string $exist, string $error_message_for_display): void {
    if ($exist === "OK") {
        // 認証成功時、W2 スタート画面へリダイレクト
        header('Location: start.php');
        exit();
    }
}
