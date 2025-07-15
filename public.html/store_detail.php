<?php
/**
 * ファイル名：store_detail.php (W8 店舗表示画面)
 * 版名：V1.1
 * 作成者：小泉 優
 * 日付：2025.07.12
 * 概要：選択された店舗を取得した店舗情報とともに表示する．
 * 対応コンポーネント：C1 UI処理部
 */

session_start();
require_once __DIR__ . '/../includes/C1/store_detail_processing.php';
require_once __DIR__ . '/../includes/C5/favorite_function.php';

$currentPlaceId = $_GET['place_id'] ?? null;
$fromPage = $_GET['from'] ?? null;
$diagnosisId = $_GET['diagnosis_id'] ?? null;

// store_detail がセッションに保存されている場合は取得
$storedStoreDetail = $_SESSION['store_detail'] ?? null;

// エラーメッセージは表示したらすぐに削除
$error = $_SESSION['error_message'] ?? null;
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

$store = null;
// 店舗詳細情報の更新ロジック
// (1) place_id がURLにない場合は処理しない
// (2) セッションに店舗情報がない、またはURLのplace_idがセッションのplace_idと異なる場合のみ更新
if ($currentPlaceId && (!isset($storedStoreDetail) || ($storedStoreDetail['place_id'] ?? '') !== $currentPlaceId)) {
    unset($_SESSION['store_detail']); // 古い店舗情報をクリア
    // 'from' パラメータに基づいて $_SESSION['back_to_page'] に遷移元情報を格納
    if ($fromPage === 'list') {
        $_SESSION['back_to_page'] = ['url' => 'search_results.php', 'text' => '店舗表示一覧に戻る'];
    } elseif ($fromPage === 'mood') {
        // mood_result.php に diagnosis_id が必要ならそれもURLに含める
        $mood_url = 'mood_result.php';
        if ($diagnosisId) {
            $mood_url .= '?diagnosis_id=' . htmlspecialchars($diagnosisId);
        }
        $_SESSION['back_to_page'] = ['url' => $mood_url, 'text' => '気分診断結果に戻る'];
    } elseif ($fromPage === 'favorite_list') {
        $_SESSION['back_to_page'] = ['url' => 'favorites.php', 'text' => 'お気に入り一覧に戻る'];
    } else {
        // from パラメータがない、または未定義の値の場合はデフォルトをセット
        $_SESSION['back_to_page'] = ['url' => 'javascript:history.back()', 'text' => '前のページに戻る'];
    }

    StoreDetailMain($currentPlaceId); // 新しい店舗情報を取得しセッションに格納
    // StoreDetailMain がエラーを発生させた場合は、$_SESSION['error_message'] が設定されるはず
    $error = $_SESSION['error_message'] ?? null; // 更新後のエラーメッセージを再取得
    if (isset($_SESSION['error_message'])) {
        unset($_SESSION['error_message']);
    }
    $store = $_SESSION['store_detail'] ?? null; // 更新後の店舗情報を再取得

} else {
    // 同じ店舗の詳細ページを再表示する場合
    // store が既にセッションにあるので、それを使う
    $store = $storedStoreDetail;
    // それ以外の場合は以前の状態を維持（unsetされていないため）

    // 再表示時も from パラメータがあればセッションを更新
    if ($fromPage === 'list') {
        $_SESSION['back_to_page'] = ['url' => 'search_results.php', 'text' => '店舗表示一覧に戻る'];
    } elseif ($fromPage === 'mood') {
        $mood_url = 'mood_result.php';
        if ($diagnosisId) {
            $mood_url .= '?diagnosis_id=' . htmlspecialchars($diagnosisId);
        }
        $_SESSION['back_to_page'] = ['url' => $mood_url, 'text' => '気分診断結果に戻る'];
    } elseif ($fromPage === 'favorite_list') {
        $_SESSION['back_to_page'] = ['url' => 'favorites.php', 'text' => 'お気に入り一覧に戻る'];
    }

}

// 最終的な戻るボタンのURLとテキストをセッションから取得
$backButtonData = $_SESSION['back_to_page'] ?? ['url' => 'javascript:history.back()', 'text' => '前のページに戻る'];

// お気に入り状態を格納する変数を初期化
$isFavorite = false;

// ユーザーがログインしており、かつ店舗情報が正しく取得できている場合
if (isset($_SESSION['user_id']) && isset($store['place_id'])) {
    // C5の制御関数を呼び出し、お気に入り状態を取得
    $statusCheck = controlFavoriteProcessing('check_status', $_SESSION['user_id'], $store['place_id']);

    // 結果が成功、かつ is_favorite が true の場合、状態を更新
    if ($statusCheck['result_status_code'] === 'SUCCESS' && $statusCheck['is_favorite']) {
        $isFavorite = true;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= isset($store['store_name']) ? htmlspecialchars($store['store_name']) . ' の店舗詳細' : '店舗詳細' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'MS Gothic', 'Meiryo', sans-serif;
            margin: 0;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            box-sizing: border-box;
            text-align: center;
        }

        .container {
            background-color: #e0f7fa;
            padding: 80px 50px 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 1200px;
            width: 90%;
            box-sizing: border-box;
            margin: 20px auto;
            position: relative;
            min-height: 700px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 150px;
        }

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

        .content-box {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            box-sizing: border-box;
        }

        h1 {
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-size: 40px;
        }

        h3 {
            font-size: 20px;
        }

        iframe {
            width: 100%;
            height: 300px;
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .review ul {
            list-style: none;
            padding-left: 0;
        }

        .review li {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .button-group {
            text-align: center;
            margin-top: 30px;
        }

        .button-group a {
            display: inline-block;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 25px;
        }

        .button-group a:hover {
            background-color: #5a6268;
        }

        .favorite-button {
            background-color: #007bff;
            color: white;
        }

        .favorite-button:hover {
            background-color: #0056b3;
        }

        .favorite-button.is-favorite {
            background-color: #ffc107;
            color: #333;
        }

        .favorite-button.is-favorite:hover {
            background-color: #e0a800;
        }

        .error-message-display {
            color: #dc3545;
            text-align: center;
        }

        p {
            font-size: 18px;
            margin: 10px 0;
        }

        p strong {
            font-size: 20px;
            font-weight: bold;
        }

        /* --- レスポンシブ対応の調整 --- */
        @media (max-width: 768px) {
            .container {
                padding: 60px 30px 25px 30px;
                max-width: 95%;
                margin-top: 10px;
                min-height: 400px;
                padding-top: 100px; /* モバイルでのパディング調整 */
            }

            .top-bar {
                top: 10px;
                padding: 0 10px;
            }

            .button-group {
                gap: 10px; /* モバイルでのボタン間隔 */
            }

            .button-group a {
                padding: 8px 15px; /* モバイルでのボタンパディング */
                min-width: unset; /* 必要に応じて最小幅をリセット */
            }
        }
    </style>
</head>

<body>
<div class="container">
    <div class="top-bar">
        <div class="title-left">店舗表示</div>
    </div>
    <div class="content-box">
        <?php if ($error): ?>
            <p class="error-message-display"><?= htmlspecialchars($error) ?></p>
            <div class="button-group">
                <a href="javascript:history.back()">前のページに戻る</a>
                <a href="../start.php">スタート画面に戻る</a>
            </div>
        <?php elseif ($store): ?>
            <h1><?= htmlspecialchars($store['store_name'] ?? '店舗詳細') ?></h1>
            <?= $store['store_photo'] ?? '' ?>
            <?= $store['store_map'] ?? '' ?>
            <p><strong>住所：</strong><?= htmlspecialchars($store['store_address'] ?? '情報なし') ?></p>
            <?php if (!empty($store['store_website'])): ?>
                <p><strong>ウェブサイト：</strong>
                    <a href="<?= htmlspecialchars($store['store_website']) ?>" target="_blank" rel="noopener">
                        <?= htmlspecialchars($store['store_website']) ?>
                    </a>
                </p>
            <?php endif; ?>
            <?= $store['store_hours'] ?? '' ?>
            <div class="review">
                <?= $store['store_review'] ?? '' ?>
            </div>
            <div class="button-group">
                <a href="<?= htmlspecialchars($backButtonData['url']) ?>" class="back-button">
                <?= htmlspecialchars($backButtonData['text']) ?>
            </a>

            <a href="handle_favorite.php?place_id=<?= htmlspecialchars($store['place_id']) ?>&from=<?= htmlspecialchars($fromPage ?? '') ?>&diagnosis_id=<?= htmlspecialchars($diagnosisId ?? '') ?>"
                   class="favorite-button <?= $isFavorite ? 'is-favorite' : '' ?>">お気に入り</a>
                <a href="start.php" style="background-color: #28a745;">スタートに戻る</a>
            </div>
        <?php else: ?>
            <p class="error-message-display">店舗詳細情報を取得できませんでした。</p>
            <div class="button-group">
                <a href="javascript:history.back()">前のページに戻る</a>
                <a href="search_form.php">検索に戻る</a>
                <a href="start.php" style="background-color: #28a745;">スタートに戻る</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
