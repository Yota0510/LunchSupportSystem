<?php
/**
 * ファイル名：mood_result.php
 * 版名：v1.1
 * 作成者：田口 陽太
 * 日付：2025.06.15
 * 機能要約：mood_diagnosis_logic.phpから受け取った診断コード(diagnosisCode)と
 * C8 診断店舗情報管理部に登録されているコードを照会して、
 * 対応する店舗の情報をW5 気分診断結果画面に表示する。
 * 対応コンポーネント：C1 UI処理部、C8 診断店舗情報管理部
 * 対応モジュール：
 */
?>
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .store-card strong {
            font-size: 1.2em;
            display: block;
            margin-bottom: 8px;
            color: #007bff;
        }
        .store-card p {
            margin: 6px 0;
            color: #333;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- C4 M3.1 推薦結果表示 -->
        <?php
        session_start();
        require_once __DIR__ . '/../includes/F/db_functions.php';

        $diagnosisCode = $_GET['code'] ?? '0000';

        if (isset($_SESSION['recommendedStores'])) {
            $recommendedStores = $_SESSION['recommendedStores'];
            unset($_SESSION['recommendedStores']); // 使い終わったらクリア
        } else {
            $recommendedStores = GetStoreInfoByDiagnosisCode($diagnosisCode);
        }

        if (count($recommendedStores) > 0): ?>
            <?php foreach ($recommendedStores as $store): ?>
                <h1>あなたにお勧めのお店は、<?= htmlspecialchars($store['store_name']) ?>です！</h1>
                <div class="store-card">
                    <strong><?= htmlspecialchars($store['store_name']) ?></strong>
                    <p>所在地: <?= htmlspecialchars($store['store_location']) ?></p>
                    <p>コメント: <?= htmlspecialchars($store['review']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">診断結果に該当する店舗が見つかりませんでした。</p>
        <?php endif; ?>

        <a href="start.php" class="back-button">スタートへ戻る</a>
    </div>
</body>
</html>
