<?php
/**
 * ファイル名；register_processing.php 
 * 版名：V1.0
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 概要: C1 UI処理部 M3 新規登録主処理のサブモジュールを実装する。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M7 新規登録主処理, M7.1 入力確認処理, M7.2 ユーザー登録処理
 */

// C2のユーザーサービスを読み込む
require_once __DIR__ . '/../C2/user_service.php';

/**
 * M7.1 新規登録情報入力処理 (CheckRegisterInput)
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 機能概要: パスワードの入力確認を行う。
 * 引数:
 * - string $password: パスワード
 * - string $password_confirm: パスワード確認用
 * 返却値:
 * - string: "OK" (正常), "NG_INPUT" (入力エラー)
 * - string $error_msg: エラーメッセージ（参照渡し）
 */
function CheckRegisterInput(string $password, string $password_confirm, ?string &$error_msg = null): string {
    $error_msg = ''; // エラーメッセージを初期化
    $has_error = false; // エラーが見つかったかどうかを示すフラグ

    // --- パスワードの空チェック ---
    if (empty($password)) {
        $error_msg .= 'パスワードが入力されていません。(E4) <br>';
        $has_error = true;
    }
    
    // --- 確認用パスワードの空チェック ---
    if (empty($password_confirm)) {
        $error_msg .= '確認用パスワードが入力されていません。(E4) <br>';
        $has_error = true;
    }
    
    // --- パスワードと確認用パスワードの一致チェック ---
    // ※空チェックの後に実行することで、空でないことを確認してから比較できる
    if (!empty($password) && !empty($password_confirm) && $password !== $password_confirm) {
        $error_msg .= 'パスワードと確認用パスワードが一致しません。(E8) <br>';
        $has_error = true;
    }
    
    // --- パスワードの長さチェック（16文字以下） ---
    // ※パスワードが空の場合はすでにエラーになっているため、空でない場合のみ長さをチェック
    if (!empty($password) && strlen($password) > 16) {
        $error_msg .= 'パスワードを16文字以下で入力してください。(E2) <br>';
        $has_error = true;
    }

    // 全てのチェックが終了した後、エラーがあったかどうかで最終的な結果を返す
    if ($has_error) {
        return "NG_INPUT";
    } else {
        return "OK";
    }
}

/**
 * M7.2 ユーザー登録処理 (RegisterNewUser)
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 機能概要: C2のユーザーサービスを介して新規ユーザーをデータベースに登録する。
 * 引数:
 * - string $password: パスワード
 * 返却値: array: ['status' => "OK" or "NG", 'user_id' => string|null]
 * エラー処理:
 * - DB登録失敗時エラー (E6に準ずる)
 */
function NewUser(string $password): array {
    // includes/C2/user_service.php の RegisterNewUser 関数を呼び出す
    return RegisterNewUser($password);
}

/**
 * M7 新規登録主処理 (RegisterMain)
 * 担当者：鈴木 馨
 * 機能概要: パスワードと確認用パスワードの入力をW10 新規登録画面から受け取り，入力の確認をM7.1で行い，新規登録を行う．
 * 引数:
 * - string $password: パスワード
 * - string $password_confirm: パスワード確認用
 * 返却値: 
 * - string $success_message : 登録成功M
 * - string $error_message : 登録失敗M
 */
function RegisterMain() {
    global $error_message, $success_message, $assigned_user_id;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ユーザーIDはフォームから受け取らない
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // --- M3.1 新規登録情報入力処理 ---
        $input_error_msg = '';
        // ユーザーIDはここでは渡さない
        $validation_result = CheckRegisterInput($password, $password_confirm, $input_error_msg);

        if ($validation_result === "NG_INPUT") {
            $error_message = $input_error_msg;
            return;
        }

        // --- M3.2 ユーザー登録処理 ---
        $register_result = NewUser($password);

        if ($register_result['status'] === "OK") {
            $assigned_user_id = $register_result['user_id']; // 割り振られたユーザーIDを取得
            $success_message = '登録が完了しました！あなたのユーザーIDは **' . htmlspecialchars($assigned_user_id) . '** です。このIDを控えてログイン画面へ移動してください。';
        } else {
            $error_message = '登録に失敗しました。(E9)';
        }
    }
}
