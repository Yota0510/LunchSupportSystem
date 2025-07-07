<?php
/**
 * ファイル名：search_form.php (W5 検索画面)
 * 版名：V1.1
 * 作成者：鈴木 馨
 * 日付：2025.07.05
 * 概要: 店舗検索の条件を入力するフォームを表示し、
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
        $validation_result['price'],
        $validation_result['distance']
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
            font-family: 'MS Gothic', 'Meiryo', 'メイリオ', sans-serif; /* フォントを統一 */
            margin: 0; /* マージンをリセット */
            background-color: #f0f2f5; /* 背景色 */
            color: #333; /* 文字色 */
            line-height: 1.6; /* 行の高さ */
            min-height: 100vh; /* 画面いっぱいの高さを確保 */
            box-sizing: border-box;
            text-align: center;
        }
        /* コンテナのスタイル */
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
            min-height: 700px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 150px; 
            box-sizing: border-box; 
        }
        /* トップバーのスタイル */
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

        h1 {
            display: none;
        }
        /* 左上のタイトル (店舗検索) のスタイル */
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
        /* フォームグループのスタイル */
        .form-group {
            margin-bottom: 20px;
            width: 100%;
            max-width: 650px;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 30px;
            padding-left: 50px;
            box-sizing: border-box;
        }
        /* ラベルのスタイル */
        label {
            margin-bottom: 0;
            font-weight: 900;
            color: #555;
            font-size: 45px;
            white-space: nowrap;
            width: 250px;
            min-width: 180px;
        }
        /* セレクトボックスのスタイル */
        select {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 30px;
            background-color: #f9f9f9;
            box-sizing: border-box;
            height: 60px;
        }
        /* 検索ボタンのスタイル */
        button[type="submit"] {
            display: block;
            width: 100%;
            max-width: 400px;
            padding: 5px 10px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 35px;
            cursor: pointer;
            transition: background-color 0.3s ease, border-color 0.3s ease;
            margin-top: 50px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            bottom: unset;
            left: unset;
        }
        /* 検索ボタンの押した時のスタイル */
        button[type="submit"]:hover {
            background-color: #f0f0f0;
            border-color: #aaa;
        }
        /* エラーメッセージのスタイル */
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        /* スタートに戻るボタンのスタイル */
        .back-button {
            display: block;
            width: 100%;
            max-width: 400px;
            padding: 5px 0px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 25px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease, border-color 0.3s ease;
            
            position: absolute;
            bottom: 20px;
            left: 5px;
            margin-top: 0;
        }
        /* スタートに戻るボタンの押した時のスタイル */
        .back-button:hover {
            background-color: #f0f0f0;
            border-color: #aaa;
        }

        /* レスポンシブ対応 */
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
            .title-left {
                font-size: 1.2em;
                padding: 8px 15px;
            }
            .form-group {
                max-width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                padding-left: 0;
            }
            .form-group label {
                font-size: 1.1em;
                width: auto;
                min-width: auto;
            }
            .form-group select {
                font-size: 1em;
                height: auto;
            }
            button[type="submit"] {
                max-width: 100%;
                margin-top: 20px;
                position: static;
                margin-left: auto;
                margin-right: auto;
            }
            .back-button {
                max-width: 100%;
                position: static;
                margin-top: 15px;
                margin-left: auto;
                margin-right: auto;
                bottom: unset;
                left: unset;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">店舗検索</div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <form action="search_form.php" method="GET">
            <div class="form-group">
                <label for="genre">ジャンル :</label>
                <select id="genre" name="genre">
                    <option value="" <?php echo (!isset($_GET['genre']) || ($_GET['genre'] ?? '') === '') ? 'selected' : ''; ?>>指定なし</option>
                    <option value="居酒屋" <?php echo ($_GET['genre'] ?? '') === '居酒屋' ? 'selected' : ''; ?>>居酒屋</option>
                    <option value="イタリアン" <?php echo ($_GET['genre'] ?? '') === 'イタリアン' ? 'selected' : ''; ?>>イタリアン</option>
                    <option value="和食" <?php echo ($_GET['genre'] ?? '') === '和食' ? 'selected' : ''; ?>>和食</option>
                    <option value="洋食" <?php echo ($_GET['genre'] ?? '') === '洋食' ? 'selected' : ''; ?>>洋食</option>
                    <option value="中華" <?php echo ($_GET['genre'] ?? '') === '中華' ? 'selected' : ''; ?>>中華</option>
                    <option value="カフェ" <?php echo ($_GET['genre'] ?? '') === 'カフェ' ? 'selected' : ''; ?>>カフェ</option>
                    <option value="ラーメン" <?php echo ($_GET['genre'] ?? '') === 'ラーメン' ? 'selected' : ''; ?>>ラーメン</option>
                    <option value="焼肉" <?php echo ($_GET['genre'] ?? '') === '焼肉' ? 'selected' : ''; ?>>焼肉</option>
                    <option value="ファストフード" <?php echo ($_GET['genre'] ?? '') === 'ファストフード' ? 'selected' : ''; ?>>ファストフード</option>
                    <option value="おにぎり" <?php echo ($_GET['genre'] ?? '') === 'おにぎり' ? 'selected' : ''; ?>>おにぎり</option>
                    <option value="和風カフェ" <?php echo ($_GET['genre'] ?? '') === '和風カフェ' ? 'selected' : ''; ?>>和風カフェ</option>
                    <option value="うなぎ" <?php echo ($_GET['genre'] ?? '') === 'うなぎ' ? 'selected' : ''; ?>>うなぎ</option>
                    <option value="パスタ" <?php echo ($_GET['genre'] ?? '') === 'パスタ' ? 'selected' : ''; ?>>パスタ</option>
                    <option value="和風定食" <?php echo ($_GET['genre'] ?? '') === '和風定食' ? 'selected' : ''; ?>>和風定食</option>
                    <option value="とんかつ" <?php echo ($_GET['genre'] ?? '') === 'とんかつ' ? 'selected' : ''; ?>>とんかつ</option>
                    <option value="サンドイッチ" <?php echo ($_GET['genre'] ?? '') === 'サンドイッチ' ? 'selected' : ''; ?>>サンドイッチ</option>
                    <option value="アサイー" <?php echo ($_GET['genre'] ?? '') === 'アサイー' ? 'selected' : ''; ?>>アサイー</option>
                    <option value="その他" <?php echo ($_GET['genre'] ?? '') === 'その他' ? 'selected' : ''; ?>>その他</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">金額 :</label>
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


            <div class="form-group">
                <label for="distance">距離 :</label>
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

            <input type="hidden" name="search" value="1">

            <button type="submit">検索</button>
        </form>
        <a href="start.php" class="back-button">スタートに戻る</a>
    </div>
</body>
</html>
