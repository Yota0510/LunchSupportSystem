<?php
/**
 * ファイル名：login.php (W1 ログイン画面)
 * 版名：V1.0
 * 作成者：小泉 優
 * 日付：2025.06.28
 * 概要: ログインフォームを表示し、ユーザーからの入力を受け付け、認証処理を行う。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M1 ログイン主処理
 */

// セッションを開始 (セッションは認証状態などを保持するために必須)
session_start();

// データベース接続設定を読み込む (getDbConnection() が必要)
require_once __DIR__ . '/../config/db_config.php';

// M1.1, M1.2, M1.3 の関数を含むファイルを読み込む
require_once __DIR__ . '/../includes/C1/login_processing.php';

$error_message = ''; // エラーメッセージ表示用変数

/**
 * LoginMain (M1 ログイン主処理)
 * 担当者：田口 陽太
 * 機能概要: W1ログイン画面から入力されたユーザIDとパスワードを取得し、
 * M1.1で入力チェックを行い、問題なければM1.2で認証要求を認証処理部に送り、
 * M1.3で認証結果に応じた画面遷移を行う。
 * 引数：
 * - string $user_id: ユーザID (W1ログイン画面より)
 * - string $password: パスワード (W1ログイン画面より)
 * 返却値：
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
        body {
            font-family: "MS Gothic", "Meiryo", "メイリオ", sans-serif; /* フォントを新規登録画面に合わせる */
            background-color: #f0f2f5; /* 背景色を新規登録画面に合わせる */
            margin: 0;
            padding: 0;
            text-align: center;
            min-height: 100vh;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container { /* .login-container から .container に変更 */
            background-color: #e0f7fa; /* 薄い水色 */
            padding: 80px 50px 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 1200px; /* 新規登録画面に合わせる */
            width: 90%;
            box-sizing: border-box;
            margin: 20px auto;

            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            min-height: 700px; /* 新規登録画面に合わせる */
        }

        .top-bar { /* 新規登録画面から追加 */
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            position: absolute;
            top: 20px;
            left: 0;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .title-left { /* 新規登録画面から追加 */
            background-color: #ffffff;
            color: #333;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 40px;
            font-weight: bold;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* フォーム要素のスタイル調整 */
        h1 {
            display: none; /* 画面中央の「ログイン」h1を非表示にする */
        }
        
        .form-content { /* フォーム要素をラップするための新しい div を想定 */
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, calc(-50% + 20px));
            gap: 15px;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
            width: 100%;
            max-width: 600px; /* 新規登録画面に合わせる */
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: nowrap;
            padding-left: 50px; /* 新規登録画面に合わせる */
            box-sizing: border-box;
        }
        
        label {
            display: inline-block;
            margin-bottom: 0;
            margin-right: 25px; /* 新規登録画面に合わせる */
            font-weight: bold;
            color: #555;
            font-size: 40px; /* 新規登録画面に合わせる */
            min-width: 250px; /* ユーザーIDとパスワードのラベルが収まるように調整 */
            white-space: nowrap;
            text-align: right;
        }
        
        input[type="text"], /* user_id用 */
        input[type="password"] {
            width: 100%;
            padding: 15px; /* 新規登録画面に合わせる */
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1.5em; /* 新規登録画面に合わせる */
            box-sizing: border-box;
            flex-grow: 1;
            max-width: 300px; /* 新規登録画面に合わせる */
        }

        button {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            background-color: #007bff; /* ログインボタンの色は維持 */
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 30px; /* 新規登録画面に合わせる */
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #dc3545;
            font-size: 25px; /* 新規登録画面に合わせる */
            margin-top: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            max-width: 600px; /* 新規登録画面に合わせる */
            margin-left: auto; /* 中央寄せのため追加 */
            margin-right: auto; /* 中央寄せのため追加 */
        }
        /* 「新規登録はこちら」リンクのスタイル */
        .form-content p a {
            font-size: 30px; /* 新規登録画面に合わせる */
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
        }


        /* レスポンシブ調整 */
        @media (max-width: 768px) {
            .container {
                padding: 60px 30px 25px 30px;
                max-width: 95%;
                margin-top: 10px;
                min-height: 400px;
            }
            .top-bar {
                top: 10px;
                padding: 0 10px;
            }
            .title-left {
                font-size: 1.2em;
                padding: 10px 20px;
            }
            h1 {
                display: none;
            }
            .form-content {
                gap: 10px;
                transform: translate(-50%, calc(-50% + 10px));
            }
            .form-group {
                flex-direction: column;
                align-items: flex-start;
                flex-wrap: wrap;
                padding-left: 0;
            }
            label {
                font-size: 1.2em;
                margin-bottom: 5px;
                margin-right: 0;
                min-width: unset;
                white-space: normal;
                text-align: left;
            }
            input[type="text"],
            input[type="password"] {
                width: calc(100% - 20px);
                max-width: unset;
                min-width: unset;
                padding: 10px;
                font-size: 1em;
            }
            button, .error-message {
                max-width: 100%;
                font-size: 1.1em;
            }
            .form-content p a {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <div class="container"> <div class="top-bar"> <div class="title-left">ログイン</div> </div>

        <div class="form-content"> <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
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
            <p style="margin-top: 20px;"><a href="register.php">新規登録はこちら</a></p>
        </div>
    </div>
</body>
</html>
