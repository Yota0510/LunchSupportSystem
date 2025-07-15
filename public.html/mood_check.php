<?php
/**
 * ファイル名：mood_check.php (W3 気分診断画面)
 * 版名：v1.1
 * 作成者：田口 陽太
 * 日付：2025.07.08
 * 機能要約：気分診断画面を表示し、質問に対する選択肢が選択されたら次の質問と選択肢を表示する。
 * すべての質問に答えられたら、mood_diagnosis_logic.phpへ気分入力（[mood(1~4)]）を渡す。
 * 対応コンポーネント：C1 UI処理部
 * 対応モジュール：M3 気分診断UI主処理、M3.1 気分診断入力処理、M3.2 診断実行要求処理、M3.3 気分診断画面表示処理、W3 気分診断画面 
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
    4 => ["text" => "食べたい料理のタイプは？", "options" => ["1" => "洋風やエスニックが食べたい", "0" => "和風や家庭的な料理がいい"]],
];

/**
 * inputMood (M3.1 気分診断入力処理)
 * 担当者：田口 陽太
 * 機能概要：POSTリクエストから質問番号と回答を取得し、セッションに保存する。
 * 入力:
 * - int $question_number: 現在の質問番号 (1~4)
 * - string $answer: ユーザの回答 (1: はい, 0: いいえ)
 * 出力:
 * - int $question_number: 次の質問番号 (1~4)
 */
function inputMood() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $question_number = intval($_POST['question_number'] ?? 1);
        $_SESSION["mood{$question_number}"] = $_POST['answer'] ?? '0';
        $question_number++;
    } else {
        $question_number = 1;
    }
    return $question_number;
}

/**
 * SendMoodCheckRequest (M3.2 気分診断実行要求処理)
 * 担当者：田口 陽太
 * 機能概要：質問が4つ終了したら、診断コードを生成し、診断結果を取得して表示画面へリダイレクトする。
 * 入力:
 * - int $question_number: 現在の質問番号 (1~4)
 */
function SendMoodCheckRequest($question_number) {
    if ($question_number > 4) {
        require_once __DIR__ . '/../includes/C4/mood_diagnosis_logic.php';
        $result = MoodCheckMain([
            'mood1' => $_SESSION['mood1'] ?? '0',
            'mood2' => $_SESSION['mood2'] ?? '0',
            'mood3' => $_SESSION['mood3'] ?? '0',
            'mood4' => $_SESSION['mood4'] ?? '0',
        ]);
        $_SESSION['recommendedStores'] = $result['storeInfo'];
        if ($result['error']) {
            // エラー表示
            echo '<p>診断処理中にエラーが発生しました。時間をおいて再度お試しください。</p>';
            exit;
        }
        header("Location: mood_result.php?diagnosis_id=" . $result['diagnosis_id']);
        exit;
    }
}

/**
 * DisplayMoodCheak (M3.3 気分診断画面表示処理)
 * 担当者：田口 陽太
 * 機能概要：現在の質問番号に応じた質問と選択肢を表示する。
 * 入力:
 * - int $question_number: 現在の質問番号 (1~4)
 * - array $question_images: 質問画像のパス
 * - array $questions: 質問と選択肢
 */
function DisplayMoodCheak($question_number, $question_images, $questions) {
    $img_path = isset($question_images[$question_number]) ? htmlspecialchars($question_images[$question_number]) : '';
    if ($img_path !== '') {
        // キャッシュ対策でタイムスタンプを付与
        $img_path .= '?v=' . time();
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
                width: 180px;
                max-width: 100%;
                box-sizing: border-box;
            }
            .button-group button:hover {
                background-color: #0056b3;
            }
            @media (max-width: 500px) {
                .button-group {
                    flex-direction: column;
                    gap: 10px;
                }
                .button-group button {
                    width: 100%;
                    min-width: 0;
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
            <?php if ($img_path): ?>
                <img src="<?php echo $img_path; ?>" alt="質問画像" class="question-image">
            <?php endif; ?>
            <!-- 質問文（テキスト）は非表示に -->
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
    <?php
}

// M3.4 気分診断画面結果表示処理（W3気分診断結果画面）
function DisplayMoodCheckResult() {
    // mood_result.phpで実装されているため、ここでは何もしない
}

/**
 * MoodCheckUIMain (M3 気分診断UI主処理)
 * 担当者：田口 陽太
 * 機能概要：気分診断のメイン処理を実行
 * 入力:
 * - array $question_images: 質問画像のパス
 * - array $questions: 質問と選択肢
 */
function MoodCheckUIMain($question_images, $questions) {
    $question_number = inputMood();
    SendMoodCheckRequest($question_number);
    DisplayMoodCheak($question_number, $question_images, $questions);
    // DisplayMoodCheckResult() は mood_result.php 側で実行
}

MoodCheckUIMain($question_images, $questions);
