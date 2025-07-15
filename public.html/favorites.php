<?php
/**
 * ファイル名：favorites.php
 * 版名：V1.1
 * 作成者：樋口 智也
 * 日付：2025.07.15
 * 機能要約：W9 お気に入り画面。ユーザーのお気に入り店舗一覧を表示し、削除機能を提供する。
 * 対応コンポーネント：C1 UI処理部
 * 対応モジュール：M6.2 お気に入り一覧表示処理
 */

session_start();

// --- 必要な関数ファイルの読み込み ---
require_once __DIR__ . '/../includes/C6/db_favorite_function.php';
require_once __DIR__ . '/../includes/C7/store_info_functions.php';
require_once __DIR__ . '/../includes/C5/favorite_function.php';

// --- 定数定義 ---
// Google Places APIキー 
define('GOOGLE_PLACES_API_KEY', 'AIzaSyDZ2e4P7njfO8tIKbAdp3_2WYZIpJH3bSo');

// --- ログイン状態の確認 ---
if ( ! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$userId = $_SESSION['user_id'];

// --- 削除アクションの処理 ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['place_id'])) {
    $placeIdToDelete = htmlspecialchars($_GET['place_id']);

    // C5の制御関数を呼び出して解除処理を実行
    controlFavoriteProcessing('deregister', $userId, $placeIdToDelete);
    
    // 自身のページにリダイレクトしてGETパラメータを消去
    header('Location: favorites.php');
    exit();
}

// --- 表示データの取得 ---
$favoriteStoreIds = getAllFavoritesByUserId($userId);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お気に入り一覧 - 昼食決めサポートシステム</title>
    <style>
        /*
         * セクション: 基本スタイル
         * ページの基本的なレイアウトやフォントを定義
         */
        body {
            font-family: "MS Gothic", "Meiryo", "メイリオ", sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        /*
         * セクション: コンテナ
         * メインコンテンツを囲むコンテナのスタイル
         */
        .container {
            max-width: 1200px;
            width: 90%;
            min-height: 700px;
            margin: 20px auto;
            padding: 20px 40px;
            background-color: #e0f7fa;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .top-bar {
            width: 100%;
            margin-bottom: 30px;
        }

        .title-left {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ffffff;
            color: #333;
            border-radius: 5px;
            font-size: 40px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /*
         * セクション: リスト関連
         * お気に入り一覧リストのスタイル
         */
        .list-container {
            width: 100%;
            flex-grow: 1;
            overflow-y: auto;
        }

        .favorite-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .favorite-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            font-size: 1.5em;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .store-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .store-link:hover {
            text-decoration: underline;
        }

        .delete-button {
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8em;
            white-space: nowrap;
        }

        /*
         * セクション: フッター
         * 戻るボタンなどを配置するフッター領域
         */
        .page-footer {
            width: 100%;
            padding-top: 20px;
            text-align: right;
        }

        .back-button {
            padding: 12px 25px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 24px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .back-button:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        /*
         * セクション: レスポンシブ対応
         * 画面幅が768px以下の場合のスタイル
         */
        @media (max-width: 768px) {
            body {
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: 100%;
                min-height: 100vh;
                margin: 0;
                padding: 20px 15px;
                border-radius: 0;
            }
            .title-left {
                padding: 10px 15px;
                font-size: 1.5em;
            }
            .favorite-item {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                font-size: 1em;
            }
            .page-footer {
                margin-top: 20px;
                text-align: center;
            }
            .back-button {
                width: 100%;
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="top-bar">
            <div class="title-left">お気に入り一覧</div>
        </header>

        <main class="list-container">
            <ul class="favorite-list">
                <?php if (empty($favoriteStoreIds)): ?>
                    <p style="text-align: center; margin-top: 50px; font-size: 1.2em; color: #555;">
                        お気に入り登録された店舗はありません。
                    </p>
                <?php else: ?>
                    <?php foreach ($favoriteStoreIds as $storeId): ?>
                        <?php
                        $details = getStoreDetailsById($storeId, GOOGLE_PLACES_API_KEY);
                        $storeName = $details['name'] ?? '店舗情報取得エラー (ID: ' . htmlspecialchars($storeId) . ')';
                        ?>
                        <li class="favorite-item">
                            <a href="store_detail.php?place_id=<?php echo htmlspecialchars($storeId); ?>&from=favorite_list" class="store-link">
                                <?php echo htmlspecialchars($storeName); ?>
                            </a>
                            <a href="favorites.php?action=delete&place_id=<?php echo htmlspecialchars($storeId); ?>" class="delete-button">削除</a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </main>
        
        <footer class="page-footer">
            <a href="start.php" class="back-button">スタート画面に戻る</a>
        </footer>
    </div>
</body>
</html>
