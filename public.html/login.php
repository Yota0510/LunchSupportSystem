<?php
/**
 * login.php (W1 ログイン画面)
 *
 * 概要: ログインフォームを表示し、ユーザーからの入力を受け付け、認証処理を行う。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M1 ログイン主処理
 */

// セッションを開始 (セッションは認証状態などを保持するために必須)
session_start();

// データベース接続設定を読み込む (getDbConnection() が必要)
require_once __DIR__ . '/../config/db_config.php';

// M1.1, M1.2, M1.3 の関数を含むファイルを読み込む
require_once __DIR__ . '/../includes/login_processing.php';

$error_message = ''; // エラーメッセージ表示用変数

/**
 * LoginMain (M1 ログイン主処理)
 *
 * 機能概要: W1ログイン画面から入力されたユーザIDとパスワードを取得し、
 * M1.1で入力チェックを行い、問題なければM1.2で認証要求を認証処理部に送り、
 * M1.3で認証結果に応じた画面遷移を行う。
 * 入力:
 * - string $user_id: ユーザID (W1ログイン画面より)
 * - string $password: パスワード (W1ログイン画面より)
 * 出力:
 * - int $auth_result: 処理の成否 (0:エラー，それ以外：正常) (※今回は画面遷移で表現)
 * - HTML: frmStart (W2 スタート画面) または LoginScreen (W1 ログイン画面)
 * エラー処理:
 * - 入力文字列が，指定された形式で入力されない場合，入力エラー（E1-E2）．
 * - 入力文字列が空欄の場合，入力エラー（E3，E4）．
 * - 通信失敗の場合，通信エラーが起きたことを報告し，W1ログイン画面に戻る．(E5)
 */
function LoginMain() {
    global $error_message; // グローバル変数 $error_message を関数内で使用するため

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- M1.1 ログイン情報入力処理 (画面からログイン情報を取得) ---
        $user_id = $_POST['user_id'] ?? '';
        $password = $_POST['password'] ?? '';

        $input_error_msg = '';
        if (!CheckLoginInput($user_id, $password, $input_error_msg)) {
            // 入力チェック失敗 (E1-E4)
            $error_message = $input_error_msg;
            // 認証結果は「失敗」として、W1ログイン画面を再表示
            DisplayLoginResult("NG", $error_message);
            return; // 処理を終了し、HTML表示へ
        }

        // --- M1.2 認証要求処理 ---
        // ユーザIDとパスワードをC2認証処理部に送り、認証結果を取得する。
        // 通信失敗 (E5) は SendAuthenticationRequest 内で処理されるか、
        // 戻り値 "NG" としてLoginMainで受け取られる。
        $auth_status = SendAuthenticationRequest($user_id, $password);

        // --- M1.3 画面遷移処理 ---
        // 認証結果に基づいて、スタート画面（W2）またはログイン画面（W1）に遷移し、必要なメッセージを表示する。
        if ($auth_status === "OK") {
            // 認証成功
            $_SESSION['user_id'] = $user_id; // ユーザーIDをセッションに保存
            DisplayLoginResult("OK", ""); // W2スタート画面へリダイレクト
            // header('Location: index.php'); // DisplayLoginResult内で処理される
            // exit();
        } else {
            // 認証失敗 (M1.2からの結果が"NG"の場合)
            $error_message = 'ユーザーIDまたはパスワードが間違っています。(E5)'; // 設計書E5も考慮
            DisplayLoginResult("NG", $error_message); // W1ログイン画面を再表示
        }
    }
}

// ログイン主処理関数を実行
LoginMain();

// ここからHTMLの記述 (C1 UI処理部の役割)
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - 昼食決めサポートシステム</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* このファイル固有のスタイル (一時的、後でstyle.cssに移動推奨) */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            color: #0056b3;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            font-size: 0.95em;
        }
        input[type="text"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>ログイン</h1>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="user_id">ユーザーID:</label>
                <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit">ログイン</button>
        </form>
        <p style="margin-top: 20px;"><a href="#">新規登録はこちら</a></p>
    </div>
</body>
</html>
