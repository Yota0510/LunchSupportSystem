<?php
/**
 * ファイル名：no_results.php (W7 該当店舗なし画面)
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
            font-family: 'MS Gothic', 'Meiryo', 'メイリオ', sans-serif; /* フォントを統一 */
            background-color: #f0f2f5; /* 背景色を統一 */
            margin: 0; /* マージンをリセット */
            min-height: 100vh; /* 画面いっぱいの高さを確保 */
            box-sizing: border-box;
            text-align: center;
            color: #333; /* テキスト色 */
            line-height: 1.6;
        }
        .container {
            background-color: #e0f7fa; /* 背景色を水色に変更 */
            padding: 80px 50px 40px 50px; /* 上部のパディングを増やしてタイトルスペースを確保 */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 1200px; /* 他の画面と幅を合わせる */
            width: 90%;
            margin: 20px auto;
            position: relative; /* 子要素の絶対配置の基準にする */
            min-height: 700px; /* ある程度の高さを確保しつつ、コンテンツが増えれば伸びる */
            display: flex; /* Flexboxを適用 */
            flex-direction: column; /* 要素を縦方向に並べる */
            align-items: center; /* 中央揃え (横方向) */
            padding-top: 150px; /* コンテンツ全体を下に移動 */
            box-sizing: border-box; /* パディングを含めて高さを計算 */
        }

        /* タイトルバー（該当店舗なし）のスタイル */
        .top-bar {
            display: flex;
            justify-content: space-between; /* 左右に配置 */
            align-items: center;
            width: 100%;
            position: absolute; /* コンテナの左上に配置 */
            top: 20px;
            left: 0;
            padding: 0 20px; /* 左右のパディング */
            box-sizing: border-box;
        }

        .title-left {
            background-color: #ffffff;
            color: #333;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 40px;
            font-weight: 900;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h1 { /* PHPで出力される既存のH1は非表示に */
            display: none;
        }
        
        .no-results-message { /* 新しいメッセージ用のクラス */
            color: #000000; 
            font-size: 50px; /* 文字を大きく */
            margin-bottom: 30px;
            font-weight: bold; /* 太字に */
            text-align: center;
            width: 100%; /* 幅を確保 */
            max-width: 600px; /* 最大幅 */
        }
        
        /* 以前の .button-group は削除またはコメントアウト */

        /* スタートに戻るボタン (左下) */
        .start-back-button {
            display: block;
            width: 100%;
            max-width: 400px; /* ボタンの横幅を統一 */
            padding: 12px 20px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-size: 25px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease, border-color 0.3s ease;

            position: absolute; /* 絶対配置 */
            bottom: 20px; /* 下からの距離 */
            left: 50px; /* 左からの距離 */
        }
        .start-back-button:hover {
            background-color: #f0f0f0;
            border-color: #aaa;
        }

        /* 検索画面に戻るボタン (右下) */
        .search-back-button {
            display: block;
            width: 100%;
            max-width: 400px; /* ボタンの横幅を統一 */
            padding: 12px 20px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-size: 25px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease, border-color 0.3s ease;

            position: absolute; /* 絶対配置 */
            bottom: 20px; /* 下からの距離 */
            right: 50px; /* 右からの距離 */
        }
        .search-back-button:hover {
            background-color: #f0f0f0;
            border-color: #aaa;
        }

        /* レスポンシブ対応 */
        @media (max-width: 768px) { /* 768pxに基準を変更 */
            .container {
                padding: 60px 30px 25px 30px;
                max-width: 95%;
                margin-top: 10px;
                min-height: 400px;
                padding-top: 100px;
            }
            .top-bar {
                top: 10px;
                padding: 0 10px;
            }
            .title-left {
                font-size: 1.2em;
                padding: 8px 15px;
            }
            .no-results-message {
                font-size: 1.3em; /* モバイルでの文字サイズ調整 */
            }
            /* モバイルではボタンを縦に積み重ねて中央に配置 */
            .start-back-button,
            .search-back-button {
                position: static; /* 絶対配置を解除 */
                margin-left: auto; /* 中央寄せ */
                margin-right: auto; /* 中央寄せ */
                max-width: 100%; /* 幅を画面いっぱいに */
                font-size: 1em; /* モバイルでのフォントサイズ調整 */
                padding: 10px 15px; /* モバイルでのパディング調整 */
            }
            /* ボタン間のマージンを調整 */
            .start-back-button {
                margin-top: 30px; /* メッセージからのマージン */
                margin-bottom: 15px; /* 次のボタンとのマージン */
            }
            .search-back-button {
                margin-bottom: 0; /* 最後のボタンなので下マージンは不要 */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">該当店舗なし</div>
        </div>

        <p class="no-results-message">該当する店舗が見つかりませんでした。</p>
        
        <a href="start.php" class="start-back-button">スタートに戻る</a>
        <a href="search_form.php" class="search-back-button">検索画面に戻る</a>
    </div>
</body>
</html>
