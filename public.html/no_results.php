<?php
/**
 * no_results.php (W7 該当店舗なし画面)
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.06.10
 * 概要: 店舗検索で条件に一致する店舗が見つからなかった場合に表示される画面。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: W7 該当店舗なし画面のUI表示
 */

session_start();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>該当店舗なし - 昼食決めサポートシステム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }
        h1 {
            color: #dc3545; /* 赤色 */
            margin-bottom: 25px;
        }
        p {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 30px;
        }
        .button-group {
            margin-top: 20px;
        }
        .button-group a {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .button-group a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>検索結果：該当店舗なし</h1>
        <p>ご指定の条件に一致する店舗は見つかりませんでした。</p>
        <div class="button-group">
            <a href="search_form.php">検索画面に戻る</a>
            <a href="index.php">スタート画面に戻る</a>
        </div>
    </div>
</body>
</html>
