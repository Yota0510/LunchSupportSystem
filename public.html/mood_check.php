<?php
/**
 * ファイル名：mood_check.php
 * 版名：v1.1
 * 作成者：田口 陽太
 * 日付：2025.06.15
 * 機能要約：気分診断画面を表示し、質問に対する選択肢が選択されたら次の質問と選択肢を表示する。
 * すべての質問に答えられたら、mood_diagnosis_logic.phpへ気分入力（[mood(1~4)]）を渡す。
 * 対応コンポーネント：C1 UI処理部
 * 対応モジュール：W3 気分診断画面 
 */
session_start();

// 質問画像のパス
$question_images = [
    1 => "img/question1.png",
    2 => "img/question2.png",
    3 => "img/question3.png",
    4 => "img/question4.png"
];

// 質問と選択肢
$questions = [
    1 => ["text" => "今のあなたのお腹の空き具合は？", "options" => ["1" => "がっつり食べたい", "0" => "ちょっと小腹が空いた"]],
    2 => ["text" => "今日はどんな気分？", "options" => ["1" => "ちょっと贅沢したい", "0" => "健康志向でいきたい"]],
    3 => ["text" => "食事の時間は？", "options" => ["1" => "ゆっくり楽しみたい", "0" => "短時間でサクッと"]],
    4 => ["text" => "にぎやかな場所に行きたい", "options" => ["1" => "洋風やエスニックが食べたい", "0" => "和風や家庭的な料理がいい"]],
];

// POST送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_number = intval($_POST['question_number'] ?? 1);
    $_SESSION["mood{$question_number}"] = $_POST['answer'] ?? '0';
    $question_number++;
} else {
    $question_number = 1;
}

// 全質問が完了したら結果ページへ
if ($question_number > 4) {
    $diagnosis_code = $_SESSION['mood1'] . $_SESSION['mood2'] . $_SESSION['mood3'] . $_SESSION['mood4'];
    header("Location: mood_result.php?code=" . $diagnosis_code);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>気分診断</title>
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
            margin-bottom: 20px;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .question-text {
            font-size: 1.3em;
            margin-bottom: 30px;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .button-group button {
            flex: 1;
            padding: 14px;
            font-size: 1.1em;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            min-width: 120px;
        }
        .button-group button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 500px) {
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div class="title-left">気分診断</div>
    </div>
        <h1>（<?php echo $question_number; ?>/4）</h1>

        <?php if (isset($question_images[$question_number])): ?>
            <img src="<?php echo htmlspecialchars($question_images[$question_number]); ?>" alt="質問画像" class="question-image">
        <?php endif; ?>

        <div class="question-text"><?php echo htmlspecialchars($questions[$question_number]['text']); ?></div>

        <form method="post">
            <input type="hidden" name="question_number" value="<?php echo $question_number; ?>">
            <div class="button-group">
                <?php foreach ($questions[$question_number]['options'] as $value => $label): ?>
                    <button type="submit" name="answer" value="<?php echo $value; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</body>
</html>
