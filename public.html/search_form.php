<?php
/**
 * ファイル名：search_form.php (W5 検索画面)
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.06.10
 * 機能要約: 店舗検索の条件を入力するフォームを表示し、
 * フォーム送信時にM4 店舗検索主処理 (StoreSearchMain) を呼び出す。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: W5 検索画面のUI表示, M4 店舗検索主処理
 */

session_start();
// search_processing.php を読み込む (下位モジュール関数を使用するため)
require_once __DIR__ . '/../includes/C1/search_processing.php';

/**
 * StoreSearchMain (C1 UI処理部 M4 店舗検索主処理)
 * 担当者: 鈴木 馨
 * 機能概要: W5検索画面で入力されたジャンル・金額・距離の条件を取得し（M4-1)，
 * 検索処理部へリクエスト送信（M4-2）．
 * 結果があれば店舗表示一覧画面（M4-3），なければ該当店舗なし画面を表示（M4-4）する．
 *
 * 引数：$input フォームからの入力データ (通常は $_GET)
 * 返却値：エラーメッセージ (HTML表示用) または空文字列
 * 成功時または該当店舗なし時は関数内でリダイレクトが行われるため、
 * この関数は戻り値を返さずに終了する。
 */
function StoreSearchMain(array $input): string
{
    $error_message = '';

    // M4.1 検索入力確認処理を呼び出し、入力の妥当性をチェックする。
    $validation_result = GetSearchCondition($input);

    // 入力に失敗した場合 (E6エラー)
    if (!$validation_result['is_valid']) {
        return $validation_result['error_message']; // エラーメッセージを呼び出し元に返す
    }

    // M4.2 店舗検索要求処理を呼び出す．
    $search_request_result = SendSearchRequest(
        $validation_result['genre'],
        $validation_result['distance'],
        $validation_result['price']
    );

    // 検索リクエストの結果ステータスに基づいて、次のアクションを決定する。
    switch ($search_request_result['status']) {
        case 'OK':
            // 検索に成功し、店舗が見つかった場合。
            // M4.3 店舗表示一覧画面表示処理を呼び出し、search_results.phpへリダイレクトする。
            DisplayStoreList($search_request_result['SearchResult']); // この関数内でexit()が呼ばれる
            break;
        case 'NO_MATCH':
            // 検索に成功したが、条件に合う店舗が見つからなかった場合。
            // M4.4 該当店舗なし画面表示処理を呼び出し、no_results.phpへリダイレクトする。
            DisplayNoResult($search_request_result['SearchResult']); // この関数内でexit()が呼ばれる
            break;
        case 'ERROR':
            // API通信失敗またはその他のAPIエラーが発生した場合 (E5エラー)。
            $error_message = $search_request_result['error_message']; // エラーメッセージを設定
            break;
    }

    return $error_message; // エラーがあった場合にのみ、エラーメッセージを返す
}


$error_message = '';

// フォームが送信された場合のみ処理を実行
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    // StoreSearchMain関数を呼び出す
    $error_message = StoreSearchMain($_GET);
    // StoreSearchMain内でリダイレクトされるため、ここにはエラーがあった場合のみ到達
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店舗検索 - 検索条件入力</title>
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
            max-width: 600px;
            margin: 30px auto;
        }
        h1 {
            color: #0056b3;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }
        button {
            display: block;
            width: 100%;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>店舗検索</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <form action="search_form.php" method="GET">
            <div class="form-group">
                <label for="genre">ジャンル:</label>
                <select id="genre" name="genre">
                    <option value="" <?php echo (!isset($_GET['genre']) || ($_GET['genre'] ?? '') === '') ? 'selected' : ''; ?>>指定なし</option>
                    <option value="居酒屋" <?php echo ($_GET['genre'] ?? '') === '居酒屋' ? 'selected' : ''; ?>>居酒屋</option>
                    <option value="イタリアン" <?php echo ($_GET['genre'] ?? '') === 'イタリアン' ? 'selected' : ''; ?>>イタリアン</option>
                    <option value="和食" <?php echo ($_GET['genre'] ?? '') === '和食' ? 'selected' : ''; ?>>和食</option>
                    <option value="中華" <?php echo ($_GET['genre'] ?? '') === '中華' ? 'selected' : ''; ?>>中華</option>
                    <option value="カフェ" <?php echo ($_GET['genre'] ?? '') === 'カフェ' ? 'selected' : ''; ?>>カフェ</option>
                    <option value="ラーメン" <?php echo ($_GET['genre'] ?? '') === 'ラーメン' ? 'selected' : ''; ?>>ラーメン</option>
                    <option value="焼肉" <?php echo ($_GET['genre'] ?? '') === '焼肉' ? 'selected' : ''; ?>>焼肉</option>
                    <option value="その他" <?php echo ($_GET['genre'] ?? '') === 'その他' ? 'selected' : ''; ?>>その他</option>
                </select>
            </div>

            <div class="form-group">
                <label for="distance">距離 (半径):</label>
                <select id="distance" name="distance">
                    <option value="0" <?php echo (!isset($_GET['distance']) || ((int)($_GET['distance'] ?? 0)) === 0) ? 'selected' : ''; ?>>指定なし</option>
                    <option value="500" <?php echo ((int)($_GET['distance'] ?? 0)) === 500 ? 'selected' : ''; ?>>〜0.5km (500m)</option>
                    <option value="1000" <?php echo ((int)($_GET['distance'] ?? 0)) === 1000 ? 'selected' : ''; ?>>〜1.0km (1000m)</option>
                    <option value="1500" <?php echo ((int)($_GET['distance'] ?? 0)) === 1500 ? 'selected' : ''; ?>>〜1.5km (1500m)</option>
                    <option value="2000" <?php echo ((int)($_GET['distance'] ?? 0)) === 2000 ? 'selected' : ''; ?>>〜2.0km (2000m)</option>
                    <option value="3000" <?php echo ((int)($_GET['distance'] ?? 0)) === 3000 ? 'selected' : ''; ?>>〜3.0km (3000m)</option>
                    <option value="5000" <?php echo ((int)($_GET['distance'] ?? 0)) === 5000 ? 'selected' : ''; ?>>〜5.0km (5000m)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">金額 (一人あたり):</label>
                <select id="price" name="price">
                    <option value="0" <?php echo (!isset($_GET['price']) || ((int)($_GET['price'] ?? 0)) === 0) ? 'selected' : ''; ?>>指定なし</option>
                    <option value="500" <?php echo ((int)($_GET['price'] ?? 0)) === 500 ? 'selected' : ''; ?>>〜500円</option>
                    <option value="1000" <?php echo ((int)($_GET['price'] ?? 0)) === 1000 ? 'selected' : ''; ?>>〜1000円</option>
                    <option value="1500" <?php echo ((int)($_GET['price'] ?? 0)) === 1500 ? 'selected' : ''; ?>>〜1500円</option>
                    <option value="2000" <?php echo ((int)($_GET['price'] ?? 0)) === 2000 ? 'selected' : ''; ?>>〜2000円</option>
                    <option value="3000" <?php echo ((int)($_GET['price'] ?? 0)) === 3000 ? 'selected' : ''; ?>>〜3000円</option>
                    <option value="5000" <?php echo ((int)($_GET['price'] ?? 0)) === 5000 ? 'selected' : ''; ?>>〜5000円</option>
                </select>
            </div>

            <input type="hidden" name="search" value="1">

            <button type="submit">検索する</button>
        </form>
        <a href="index.php" class="back-button">スタート画面に戻る</a>
    </div>
</body>
</html>
