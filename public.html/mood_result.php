<?php
/**
 * ファイル名：mood_result.php
 * 版名：v1.0
 * 作成者：田口 陽太
 * 日付：2025.06.15
 * 機能要約：mood_diagnosis_logic.phpから受け取った店舗情報を気分診断結果画面に表示する。
 * 対応コンポーネント：C1 UI処理部、C8 診断店舗情報管理部
 */

session_start();
require_once __DIR__ . '/../includes/C4/mood_diagnosis_logic.php';
$recommendedStores = [];

if (isset($_GET['diagnosis_id'])) {
    // 診断IDから店舗情報を再取得
    $recommendedStores = DiagnosisInquiry($_GET['diagnosis_id']);
} elseif (isset($_SESSION['recommendedStores'])) {
    $recommendedStores = $_SESSION['recommendedStores'];
    unset($_SESSION['recommendedStores']);
}

/**
 * DisplayMoodCheckResult (M3.4 気分診断画面結果表示処理)
 * 担当者：田口 陽太
 * 機能概要：診断結果を表示するHTMLを生成する。
 * 入力:
 * - array $recommendedStores: 推薦された店舗情報の配列
 */
function DisplayMoodCheckResult($recommendedStores) {
?>
<!-- M3.4 気分診断画面結果表示処理（W3気分診断結果画面） -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>診断結果</title>
    <style>
        body {
            font-family: "MS Gothic", "Meiryo", "メイリオ", sans-serif;
            background-color: #f2f2f2;
            padding: 0;
            margin: 0;
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
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .store-card {
            background-color: #fafafa;
            padding: 36px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 28px;
            text-align: center;
        }
        .store-card strong {
            font-size: 2.1em;
            font-weight: bold;
            display: block;
            margin-bottom: 18px;
            color: #007bff;
            letter-spacing: 1px;
        }
        .store-card p {
            margin: 10px 0 0 0;
            color: #333;
            font-size: 1.35em;
            font-weight: 500;
        }
        .back-button {
            display: block;
            width: 100%;
            padding: 14px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 30px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
        .button-row-bottom {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            gap: 20px;
        }
        .button-row-bottom .back-button {
            width: 220px;
            min-width: 180px;
            max-width: 100%;
            margin-top: 0;
            padding: 14px;
            font-size: 1.1em;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .button-row-bottom .back-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        @media (max-width: 500px) {
            .button-row-bottom {
                flex-direction: column;
                gap: 10px;
            }
            .button-row-bottom .back-button {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">気分診断結果</div>
        </div>
        <!-- C4 M3.1 推薦結果表示 -->
        <?php if (count($recommendedStores) > 0): ?>
            <?php foreach ($recommendedStores as $store): ?>
                <h1 style="margin-top:70px;">あなたにお勧めのお店は・・・</h1>
                <div class="store-card">
                    <strong><?= htmlspecialchars($store['store_name']) ?></strong>
                    <p>所在地: <?= htmlspecialchars($store['store_location']) ?></p>
                </div>
            <?php endforeach; ?>
            <div class="button-row-bottom">
                <form action="store_detail.php" method="get" style="margin:0;">
                    <input type="hidden" name="place_id" value="<?= htmlspecialchars($recommendedStores[0]['store_id'] ?? '') ?>">
                    <?php if (isset($_GET['diagnosis_id'])): ?>
                        <input type="hidden" name="diagnosis_id" value="<?= htmlspecialchars($_GET['diagnosis_id']) ?>">
                    <?php endif; ?>
                    <input type="hidden" name="from" value="mood">
                    <button type="submit" class="back-button"<?= empty($recommendedStores[0]['store_id'] ?? '') ? ' disabled' : '' ?>>店舗詳細を見る</button>
                </form>
                <a href="start.php" class="back-button">スタートへ戻る</a>
            </div>
        <?php else: ?>
            <h1 style="margin-top:70px;">診断結果</h1>
            <p style="text-align:center;">診断結果に該当する店舗が見つかりませんでした。</p>
            <div class="button-row-bottom">
                <a href="start.php" class="back-button">スタートへ戻る</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
}

DisplayMoodCheckResult($recommendedStores);
