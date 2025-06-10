<?php
/**
 * search_results.php (W6 店舗表示一覧画面)
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.06.10
 * 概要: 店舗検索の条件が一致する店舗一覧を表示する
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: W5 検索画面のUI表示, M4.3 店舗表示一覧画面表示処理
 */

// 必要なモジュールを読み込む
require_once __DIR__ . '/../includes/C1/search_processing.php';

// フォームから送信された値を取得
$search_condition = GetSearchCondition($_GET);

$genre = $search_condition['genre']; // 選択されたジャンル
$distance_param = $search_condition['distance']; // 検索で指定された距離 (メートル単位)
$price = $search_condition['price']; // 選択された金額

$search_results = [];
$api_error_message = '';


if ($search_condition['is_valid']) {
    $result = SendSearchRequest($genre, $price, $distance_param);

    if ($result['status'] === 'OK') {
        $search_results = $result['SearchResult'];
    } else {
        // 'NO_MATCH' の場合は search_form.php 側で no_results.php へリダイレクトされるため、
        // search_results.php に到達する場合は 'ERROR' のみ。
        $api_error_message = $result['error_message'];
    }
} else {
    // search_form.php での入力エラーの場合。
    // search_form.php でエラーメッセージを表示し、このページにはリダイレクトされないはずだが、
    // 万が一、search_results.php に直接不正なGETパラメータでアクセスされた場合の防御として残す。
    $api_error_message = $search_condition['error_message'];
}



// 表示用の距離文字列を準備
// 検索条件の距離は km 単位で表示
$display_distance_km = $distance_param / 1000; 

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店舗検索結果</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 30px auto;
        }
        h1, h2 {
            color: #0056b3;
            text-align: center;
            margin-bottom: 20px;
        }

        .store-item {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column; /* 縦方向に要素を並べる */
            gap: 10px; /* 要素間のスペース */
        }
        .store-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .store-name {
            font-weight: bold;
            font-size: 1.5em; /* 少し大きめ */
            color: #333;
            flex-grow: 1;
        }
        .store-name a {
            color: #007bff;
            text-decoration: none;
        }
        .store-name a:hover {
            text-decoration: underline;
        }
        .store-rating {
            font-size: 1em;
            color: #777;
            white-space: nowrap;
        }
        .store-details-group { /* 新しいクラス名 */
            font-size: 0.95em;
            color: #555;
            margin-top: 5px; /* 上の要素との間のスペース */
            line-height: 1.8; /* 行の高さ */
            display: flex; /* 各情報を横に並べるためにFlexboxを使用 */
            flex-wrap: wrap; /* 必要に応じて折り返す */
            gap: 10px 20px; /* 縦方向10px, 横方向20pxのギャップ */
        }
        /* 各情報項目はspanタグでそのまま */

        .no-results, .error-message {
            text-align: center;
            padding: 20px;
            border: 1px solid #ffc107;
            background-color: #fff3cd;
            color: #856404;
            border-radius: 5px;
            margin-top: 20px;
        }
        .error-message {
            border-color: #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }
        .back-button {
            display: block;
            width: 100%;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #5a6268;
        }

        /* レスポンシブ対応 */
        @media (max-width: 600px) {
            .store-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .store-name {
                width: 100%;
                text-align: left;
            }
            .store-rating {
                width: 100%;
                text-align: left;
            }
            .store-details-group {
                flex-direction: column; /* スマホでは縦並び */
                gap: 5px; /* 縦並び時の間隔 */
            }
            /* .detail-item はspanタグなので特に幅の指定は不要 */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>店舗検索結果</h1>

        <?php if (!empty($api_error_message)): ?>
            <div class="error-message">
                <p><?php echo $api_error_message; ?></p>
            </div>
        <?php elseif (empty($search_results)): ?>
            <?php else: ?>
            <h2>見つかった店舗 (<?php echo count($search_results); ?>件):</h2>
            <?php foreach ($search_results as $store): ?>
                <div class="store-item">
                    <div class="store-header">
                        <div class="store-name">
                            <a href="store_detail.php?place_id=<?php echo htmlspecialchars($store['place_id']); ?>">
                                <?php echo htmlspecialchars($store['name']); ?>
                            </a>
                        </div>
                        <?php if (isset($store['rating'])): ?>
                            <div class="store-rating">評価: <?php echo htmlspecialchars($store['rating']); ?> / 5.0</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="store-details-group">
                        <span class="detail-item">ジャンル: <?php echo !empty($genre) ? htmlspecialchars($genre) : '指定なし'; ?></span>
                        <span class="detail-item">金額: <?php
                            if ($price == 0) {
                                echo '指定なし';
                            } elseif ($price >= 5000) {
                                echo '5000円以上';
                            } else {
                                echo '〜' . htmlspecialchars($price) . '円';
                            }
                        ?></span>
                        <span class="detail-item">距離: <?php echo htmlspecialchars($display_distance_km); ?>km以内</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="search_form.php" class="back-button">検索画面に戻る</a>
    </div>
</body>
</html>
