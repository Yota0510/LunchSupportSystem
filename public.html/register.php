<?php
/**
 * ファイル名：register.php (W10 新規登録画面)
 * 版名：V1.0
 * 担当者：鈴木 馨
 * 日付：2025.06.28
 * 概要: 新規ユーザー登録フォームを表示し、ユーザーからの入力を受け付け、認証処理を行う。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M7 新規登録主処理
 */

session_start();

// 必要なファイルを読み込む
require_once __DIR__ . '/../includes/C1/register_processing.php';

$error_message = '';
$success_message = '';
$assigned_user_id = ''; // 割り振られたユーザーIDを保持する変数


// メイン処理の実行
RegisterMain();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 - 昼食決めサポートシステム</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: "MS Gothic", "Meiryo", "メイリオ", sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            text-align: center;
            min-height: 100vh;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: #e0f7fa; /* 薄い水色 */
            padding: 80px 50px 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            width: 90%;
            box-sizing: border-box;
            margin: 20px auto;

            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            min-height: 700px; /* コンテナの最小高さを確保 */
        }

        .top-bar {
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

        .title-left {
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
            display: none; /* 画面中央の「新規登録」h1を非表示にする */
        }
        
        .form-content {
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
            max-width: 600px; /* フォームグループ全体の最大幅を広げる */
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: center; /* 垂直方向の中央揃え */
            justify-content: flex-start; /* 左寄せ */
            flex-wrap: nowrap; /* ラベルと入力ボックスが改行されないようにする */
            padding-left: 50px; /* ラベルを左にずらすためのパディング */
            box-sizing: border-box; /* パディングを含めて幅を計算 */
        }
        
        label {
            display: inline-block;
            margin-bottom: 0;
            margin-right: 5px; /* 入力ボックスとの間隔を広げる */
            font-weight: bold;
            color: #555;
            font-size: 35px;
            min-width: 350px; /* ラベルの最小幅を維持し、改行を防ぐ */
            white-space: nowrap; /* ラベル内で改行されないようにする */
            text-align: left; /* ラベルのテキストを右寄せにして、入力ボックスとの間隔を視覚的に揃える */
        }
        
        input[type="password"] {
            width: 100%; /* 親要素（.form-group）の残りスペースを埋める */
            padding: 15px; /* パディングを増やしてボックスを大きくする */
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1.5em; /* 入力文字のサイズを大きくする */
            box-sizing: border-box;
            flex-grow: 1; /* 残りのスペースを埋めるように伸縮 */
            max-width: 300px; /* 入力ボックスの最大幅を設定し、揃える */
        }

        button {
            width: 100%;
            max-width: 400px;
            padding: 8px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        button:hover {
            background-color: #218838;
        }
        .error-message {
            color: #dc3545;
            font-size: 25px;
            margin-top: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            max-width: 600px; /* フォームグループの最大幅に合わせる */
            margin-left: auto;
            margin-right: auto;
        }
        .success-message {
            color: #28a745;
            font-size: 25px;
            margin-top: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            max-width: 700px; /* フォームグループの最大幅に合わせる */
            margin-left: auto;
            margin-right: auto;
        }
        .user-id-display {
            font-size: 25px;
            font-weight: bold;
            color: #0056b3;
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #0056b3;
            border-radius: 5px;
            background-color: #e7f3ff;
            width: 100%;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .form-content p a { /* form-content内のpタグに続くaタグに適用 */
            font-size: 25px; /* ここで文字サイズを調整してください */
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
                flex-direction: column; /* モバイルでは縦並びに戻す */
                align-items: flex-start;
                flex-wrap: wrap; /* 必要に応じて改行を許可 */
                padding-left: 0; /* モバイルではパディングをリセット */
            }
            label {
                font-size: 1.2em;
                margin-bottom: 5px;
                margin-right: 0;
                min-width: unset; /* モバイルでは最小幅を解除 */
                white-space: normal; /* モバイルでは改行を許可 */
                text-align: left; /* モバイルでは左寄せに戻す */
            }
            input[type="password"] {
                width: calc(100% - 20px);
                max-width: unset; /* モバイルでは最大幅を解除 */
                min-width: unset; /* モバイルでは最小幅を解除 */
                padding: 10px; /* モバイルでのパディングを調整 */
                font-size: 1em; /* モバイルでの入力文字サイズを調整 */
            }
            button, .error-message, .success-message, .user-id-display {
                max-width: 100%;
                font-size: 1.1em; /* モバイルでのボタンやメッセージの文字サイズを調整 */
            }
            .form-content p a {
                font-size: 1.2em; /* モバイル時の文字サイズを調整 */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">新規登録</div>
        </div>

        <div class="form-content">
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <p class="success-message">
                    <?php echo $success_message; ?>
                </p>
                <p><a href="login.php">ログイン画面へ</a></p>
            <?php endif; ?>

            <?php if (empty($success_message)): ?>
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="password">パスワード:</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">パスワード (確認用):</label>
                        <input type="password" id="password_confirm" name="password_confirm">
                    </div>
                    <button type="submit">登録</button>
                </form>
                <p style="margin-top: 20px;"><a href="login.php">ログイン画面へ戻る</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
