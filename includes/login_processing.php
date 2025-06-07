<?php
/**
 * login_processing.php
 *
 * 概要: C1 UI処理部 M1 ログイン主処理のサブモジュールを実装する。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M1.1 入力確認処理, M1.2 認証要求処理, M1.3 画面遷移処理
 */

// 認証関連関数を読み込む (M1.2 認証要求処理で必要)
require_once __DIR__ . '/auth_functions.php';

/**
 * CheckLoginInput (M1.1 入力確認処理)
 *
 * 機能概要: ユーザIDとパスワードの入力の確認を行う。
 * 入力:
 * - string $user_id: ユーザID (W1ログイン画面より)
 * - string $password: パスワード (W1ログイン画面より)
 * 出力:
 * - bool $is_valid: 入力が正しければtrue、そうでなければfalse
 * - string $error_msg: エラーメッセージ（入力エラー時）
 * エラー処理:
 * - 入力文字列が空欄の場合、入力エラー（E3，E4）としてエラーメッセージを返す。
 */
function CheckLoginInput(string $user_id, string $password, ?string &$error_msg = null): bool {
    $is_valid = true;
    $error_msg = '';

    if (empty($user_id)) {
        $error_msg .= 'ユーザーIDが入力されていません。(E3) ';
        $is_valid = false;
    }
    if (empty($password)) {
        $error_msg .= 'パスワードが入力されていません。(E4) ';
        $is_valid = false;
    }

    // ここにさらに、指定された形式の入力チェック（例: 文字数制限、使用可能文字など）を追加
    // 例:
    // if (strlen($user_id) > 20) {
    //     $error_msg .= 'ユーザーIDが長すぎます。(E1) ';
    //     $is_valid = false;
    // }
    // if (!preg_match('/^[a-zA-Z0-9]+$/', $user_id)) {
    //     $error_msg .= 'ユーザーIDに無効な文字が含まれています。(E2) ';
    //     $is_valid = false;
    // }

    return $is_valid;
}

/**
 * SendAuthenticationRequest (M1.2 認証要求処理)
 *
 * 機能概要: ユーザIDとパスワードをC2認証処理部に送り、認証結果を取得する。
 * 入力:
 * - string $user_id: ユーザID (M1 ログイン主処理より)
 * - string $password: パスワード (M1 ログイン主処理より)
 * 出力:
 * - string $exist: 認証成功なら"OK"、失敗なら"NG"
 * エラー処理:
 * - 通信失敗の場合、通信エラーが起きたことを報告し、W1ログイン画面に戻る。(E5)
 */
function SendAuthenticationRequest(string $user_id, string $password): string {
    // 実際にはC2認証処理部 (auth_functions.phpのauthenticateUser関数) を呼び出す
    // 通信エラーはPHPの内部関数呼び出しなので、このレイヤーでは直接発生しないが、
    // authenticateUser内部でのDB接続エラーなどは考慮する必要がある。
    // その場合、authenticateUserがfalseを返すことで「認証失敗」と判断する。

    if (authenticateUser($user_id, $password)) {
        return "OK"; // 認証成功
    } else {
        return "NG"; // 認証失敗
    }
}

/**
 * DisplayLoginResult (M1.3 画面遷移処理)
 *
 * 機能概要: 認証結果に基づいて、スタート画面（W2）またはログイン画面（W1）に遷移し、必要なメッセージを表示する。
 * 入力:
 * - string $exist: "OK" または "NG" (M1 ログイン主処理より)
 * - string $error_message_for_display: 表示するエラーメッセージ (認証失敗時)
 * 出力:
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
        header('Location: index.php');
        exit();
    } else {
        // 認証失敗時、W1 ログイン画面を再表示し、エラーメッセージを渡す
        // ここでは直接HTMLを出力せず、呼び出し元 (login.php) でエラーメッセージを表示させる。
        // （画面遷移処理の機能概要に「必要なメッセージを表示する」とあるため、
        //   login.php側で $error_message 変数にセットする形にする）
        // return $error_message_for_display; // この関数からエラーメッセージを返すこともできるが、今回は直接処理しない。
        // HTML出力はlogin.phpのUI処理部が行うため、ここでは何もしない。
    }
}
