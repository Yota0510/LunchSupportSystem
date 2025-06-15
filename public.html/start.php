<?php
/**
 * start.php (W2 スタート画面 - C1 UI処理部, C2 スタート処理部 M1 スタート主処理)
 *
 * 概要: スタート画面を表示し、各機能へのリンクを提供する
 * 対応コンポーネント: C1 UI処理部, C2 スタート処理部 M1 スタート主処理
 */

session_start(); // セッションを開始

// 認証チェック
// ログインしていない場合はログイン画面へリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = htmlspecialchars($_SESSION['user_id']); 

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタート画面 - 昼食決めサポートシステム</title>
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
        
        .favorite-button a {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease, border-color 0.3s ease;
            
            font-size: 40px;    /*お気に入り一覧ボタンのサイズ*/
            display: inline-block;
            width: auto;
            max-width: none;
            box-sizing: border-box;
            text-align: center;
        }
        .favorite-button a:hover {
            background-color: #f0f0f0;
            transform: translateY(-3px);
            border-color: #aaa;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 50px; /* ボタン間の間隔 */
            align-items: center;
            width: 100%;
            
            /* ここから追加・変更 */
            position: absolute; /* 親 (container) に対して絶対配置 */
            top: 50%; /* 親の真ん中にボタン群の上端が来るように */
            left: 50%; /* 親の真ん中にボタン群の左端が来るように */
            transform: translate(-50%, calc(-50% + 20px)); /* 自分自身の半分戻し + ボタン間隔の半分上にずらす */
            /* 20pxはgapの半分（ボタンの中央を合わせるため） */
            /* もしボタンの高さが大きく変わるなら調整が必要 */
            /* または、もっと簡単な方法として、flex-grow を利用する */
        }
        
        /* 別の方法：Flexboxを使って垂直方向中央配置 */
        /* .container に既に display: flex; flex-direction: column; が設定されているので、
           残りのスペースを `flex-grow: 1;` で埋めるダミー要素を挟むことで、
           button-group を中央に配置しやすくなります。
           HTMLの変更が必要です。後述します。
        */

        .button-group a {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            display: block;
            width: 100%;
            max-width: 400px;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease, border-color 0.3s ease;
            box-sizing: border-box;
        }

        /* 気分診断と店舗検索ボタンのサイズ */
        .button-group a:nth-child(1), /* 気分診断 */
        .button-group a:nth-child(2) /* 店舗検索 */
        {
            font-size: 50px;
        }
        
        .button-group a:hover {
            background-color: #f0f0f0;
            transform: translateY(-3px);
            border-color: #aaa;
        }

        /* レスポンシブ対応は維持しつつ調整 */
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
            .favorite-button a {
                font-size: 12px;
                padding: 6px 12px;
            }
            .button-group {
                gap: 15px; /* モバイルでの間隔 */
                /* transform の調整 (モバイル用) */
                transform: translate(-50%, calc(-50% + 6px)); /* 15pxの半分 */
            }
            .button-group a {
                padding: 10px 20px;
            }
            .button-group a:nth-child(1),
            .button-group a:nth-child(2)
            {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">スタート</div>

            <div class="favorite-button">
                <a href="favorites.php">お気に入り一覧</a>
            </div>
        </div>

        <div class="button-group">
            <a href="mood_check.php">気分診断</a>
            <a href="search_form.php">店舗検索</a>
        </div>
    </div>
</body>
</html>
