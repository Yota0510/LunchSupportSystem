<?php
/**
 * ファイル名：search_results.php (W6 店舗表示一覧画面)
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.07.15
 * 概要: 店舗検索の条件が一致する店舗一覧を表示する
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: W5 検索画面のUI表示, M4.3 店舗表示一覧画面表示処理
 */

session_start();

// 必要なモジュールを読み込む
require_once __DIR__ . '/../includes/C1/search_processing.php';

$search_results = [];
$api_error_message = '';

// 検索条件の取得ロジック
// 1. GETパラメータから検索条件を取得しようと試みる（フォームからの直接遷移）
// 2. GETパラメータがない場合、セッションから以前の検索条件を読み込む（店舗詳細から戻るなど）
if (!empty($_GET)) { 
    $search_condition = GetSearchCondition($_GET);
    // 正常な検索条件であればセッションに保存
    if ($search_condition['is_valid']) {
        $_SESSION['last_search_condition'] = $search_condition;
    } else {
        // GETパラメータはあったが不正だった場合（例：一部欠けているなど）
        // セッションの検索条件をクリアして、エラー表示を優先
        unset($_SESSION['last_search_condition']);
    }
} elseif (isset($_SESSION['last_search_condition'])) { 
    // GETパラメータがないが、セッションに前回の検索条件がある場合
    $search_condition = $_SESSION['last_search_condition'];
    // セッションから読み込んだ場合でも有効性を再確認（念のため）
    // GetSearchCondition は $_GET を前提としているため、ここでは直接バリデーションを適用する
    // または、GetSearchCondition を $_SESSION からも読み込めるように修正する
    // ここではシンプルに、セッションのデータが有効と仮定して進めるが、
    // 必要であればここでさらにバリデーションロジックを追加
    $search_condition['is_valid'] = true; // セッションに存在すれば有効と見なす
} else { 
    // GETパラメータもセッションにも検索条件がない場合（初回アクセスや直接アクセス）
    // デフォルトの無効な検索条件を設定し、エラーメッセージを発生させる
    $search_condition = [
        'is_valid' => false,
        'error_message' => 'ジャンル、距離、金額のいずれか一つ以上を選択してください。(E6)',
        'genre' => '',
        'distance' => 0,
        'price' => 0
    ];
}

$genre = $search_condition['genre'];
$distance_param = $search_condition['distance'];
$price = $search_condition['price'];

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
            font-family: 'MS Gothic', 'Meiryo', 'メイリオ', sans-serif; /* フォントを統一 */
            margin: 0; /* マージンをリセット */
            background-color: #f0f2f5; /* 背景色 */
            color: #333;
            line-height: 1.6;
            min-height: 100vh; /* 画面いっぱいの高さを確保 */
            box-sizing: border-box;
            text-align: center;
        }
        .container {
            background-color: #e0f7fa; /* 背景色を水色に変更 */
            padding: 80px 50px 40px 50px; /* 上部のパディングを増やしてタイトルスペースを確保 */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 1200px; /* 検索フォームと幅を合わせる */
            width: 90%;
            box-sizing: border-box;
            margin: 20px auto;
            position: relative; /* 子要素の絶対配置の基準にする */
            min-height: 700px; /* ある程度の高さを確保しつつ、検索結果が増えれば伸びる */
            display: flex; /* Flexboxを適用 */
            flex-direction: column; /* 要素を縦方向に並べる */
            align-items: center; /* 中央揃え (横方向) */
            padding-top: 150px; /* コンテンツ全体を下に移動 */
        }
        
        /* タイトルバー（店舗表示一覧）のスタイル */
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

        h1 { /* PHPで出力される店舗検索結果のH1は非表示 */
            display: none;
        }
        
        h2 { /* 見つかった店舗数表示のH2 */
            color: #0056b3;
            text-align: center;
            margin-bottom: 20px;
            margin-top: 0; /* 上部の余白をリセット */
            font-size: 25px;
        }

        .store-list { /* 店舗アイテムを囲むdivを追加 */
            width: 100%; /* 親要素の幅いっぱいに */
            max-width: 800px; /* 店舗アイテムの最大幅に合わせる */
            margin-bottom: 80px; /* 戻るボタンとのスペースを確保 */
        }

        .store-item {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left; /* テキストを左揃えにする */
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
            font-size: 25px;
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
            font-size: 20px;
            color: #777;
            white-space: nowrap;
            font-weight: bold;
        }
        .store-details-group {
            font-size: 18px;
            color: #555;
            margin-top: 5px;
            line-height: 1.8;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 20px;
            font-weight: bold;
        }

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
        
        /* 検索画面に戻るボタンのスタイル調整 */
        .back-button {
            display: block;
            width: 100%; /* 親要素の幅に合わせる */
            max-width: 400px; /* ここで横幅を統一 (search_form.php と合わせる) */
            padding: 5px 0px; /* 検索フォームのボタンとパディングを合わせる */
            background-color: #ffffff; /* 白に変更 */
            color: #000000; /* 黒に変更 */
            border: 1px solid #ddd; /* 枠線を追加 */
            border-radius: 4px;
            font-size: 25px; /* 検索フォームのボタンとフォントサイズを合わせる */
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease, border-color 0.3s ease; /* ホバー時のトランジション */

            position: absolute; /* 絶対配置 */
            bottom: 20px; /* 画面下からの距離 */
            left: 50px; /* 画面左からの距離 */
            margin-top: 0; /* 不要なマージンをリセット */
        }
        .back-button:hover {
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
                padding-top: 100px; /* モバイルでのパディングも調整 */
            }
            .top-bar {
                top: 10px;
                padding: 0 10px;
            }
            .title-left {
                font-size: 1.2em;
                padding: 8px 15px;
            }
            .store-item {
                padding: 10px; /* モバイルでのパディングを小さく */
            }
            .store-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .store-name {
                width: 100%;
                text-align: left;
                font-size: 1.3em; /* モバイルでのフォントサイズ調整 */
            }
            .store-rating {
                width: 100%;
                text-align: left;
                font-size: 0.9em; /* モバイルでのフォントサイズ調整 */
            }
            .store-details-group {
                flex-direction: column;
                gap: 5px;
                font-size: 0.9em; /* モバイルでのフォントサイズ調整 */
            }
            .back-button {
                max-width: 100%; /* モバイルでは横幅100% */
                position: static; /* 絶対配置解除 */
                margin-top: 15px;
                margin-left: auto;
                margin-right: auto;
                bottom: unset;
                left: unset;
                font-size: 1em; /* モバイルでのフォントサイズ調整 */
                padding: 10px 15px; /* モバイルでのパディング調整 */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">店舗表示一覧</div>
        </div>

        <?php if (!empty($api_error_message)): ?>
            <div class="error-message">
                <p><?php echo $api_error_message; ?></p>
            </div>
        <?php elseif (empty($search_results)): ?>
            <div class="no-results">
                <p>該当する店舗が見つかりませんでした。</p>
            </div>
        <?php else: ?>
            <h2>見つかった店舗 (<?php echo count($search_results); ?>件):</h2>
            <div class="store-list"> <?php foreach ($search_results as $store): ?>
                <div class="store-item">
                    <div class="store-header">
                        <div class="store-name">
                            <a href="store_detail.php?place_id=<?php echo htmlspecialchars($store['place_id']); ?>&from=list">
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
                                echo '～5000円';
                            } else {
                                echo '〜' . htmlspecialchars($price) . '円';
                            }
                        ?></span>
                        <span class="detail-item">距離: <?php echo htmlspecialchars($store['distance']); ?>m</span>
                    </div>
                </div>
            <?php endforeach; ?>
            </div> <?php endif; ?>

        <a href="search_form.php" class="back-button">検索に戻る</a>
    </div>
</body>
</html>
