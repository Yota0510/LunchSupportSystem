<?php
// index.php (W2 スタート画面 - C1 UI処理部, C2 スタート処理部 M1 スタート主処理)

session_start(); // セッションを開始

// 認証チェック
// ログインしていない場合はログイン画面へリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$loggedInUserId = htmlspecialchars($_SESSION['user_id']); // ログイン中のユーザーIDを取得 (XSS対策)

// 共通ヘッダーを読み込む (まだ作成していなければ、この行はコメントアウトするか、後で追加)
// require_once __DIR__ . '/../includes/header.php';

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタート画面 - 昼食決めサポートシステム</title>
    <link rel="stylesheet" href="css/style.css">
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
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 50px auto;
        }
        h1 {
            color: #333;
            margin-bottom: 25px;
        }
        p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 30px;
        }
        .button-group a {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .button-group a:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>ようこそ、<?php echo $loggedInUserId; ?>さん！</h1>
        <p>昼食決めをサポートするシステムです。以下の機能から選択してください。</p>

        <div class="button-group">
            <a href="mood_check.php">気分診断をする</a>
            <a href="search_form.php">店舗を検索する</a>
            <a href="favorites.php">お気に入りを見る</a>
        </div>
    </div>
</body>
</html>
<?php
// 共通フッターを読み込む (まだ作成していなければ、この行はコメントアウトするか、後で追加)
// require_once __DIR__ . '/../includes/footer.php';
?>
