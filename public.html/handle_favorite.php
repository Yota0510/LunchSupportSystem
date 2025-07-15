<?php
/**
 * ファイル名：handle_favorite.php
 * 版名：V1.1
 * 作成者：樋口 智也
 * 日付：2025.07.15
 * 機能要約：お気に入り登録/解除の要求を受け付け、C5コンポーネントを呼び出す処理ファイル。
 * 対応コンポーネント：C1 UI処理部
 * 対応モジュール：M6.1 お気に入り登録/解除処理
 */

// 開発中はエラーを全て表示
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ユーザーIDをセッションから取得するため、最初にセッションを開始
session_start();

// --- 必要な関数ライブラリを読み込み  ---
// C5(処理層)の関数を呼び出すために必要
require_once __DIR__ . '/../includes/C5/favorite_function.php';


// --- ログイン状態の確認 ---
// ログインしていないユーザーはお気に入り機能を使えないため、ログインページに移動
if ( ! isset($_SESSION['user_id'])) { // 単文でも"{"と"}"でくくる 
    header('Location: login.php');
    exit();
}
$userId = $_SESSION['user_id']; // ログイン中のユーザーIDを変数に格納 


// --- URLパラメータの正当性チェック ---
// 必要なパラメータがURLに含まれていない場合は、不正なリクエストとして処理を停止
if (!isset($_GET['place_id'])) {
    die('不正なリクエストです。');
}

// URLから受け取ったパラメータを変数に格納
$placeId = htmlspecialchars($_GET['place_id']);

// 元のURLから 'from' パラメータを取得
$fromParam = '';
if (isset($_GET['from']) && in_array($_GET['from'], ['list', 'mood', 'favorite_list'])) {
    // 許可された 'from' 値のみを引き継ぐ (例: list, mood, favorite_list)
    $fromParam = '&from=' . urlencode($_GET['from']);
}

// 元のURLから 'diagnosis_id' パラメータを取得
$diagnosisIdParam = '';
if (isset($_GET['diagnosis_id'])) {
    $diagnosisIdParam = '&diagnosis_id=' . urlencode($_GET['diagnosis_id']);
}

// current_page パラメータを取得
$currentPage = $_GET['current_page'] ?? null;

// --- メイン処理 ---
// お気に入り状態のトグル処理
$statusCheck = controlFavoriteProcessing('check_status', $userId, $placeId);

if ($statusCheck['result_status_code'] === 'SUCCESS') {
    $actionType = $statusCheck['is_favorite'] ? 'deregister' : 'register';
    controlFavoriteProcessing($actionType, $userId, $placeId);
} else {
    // エラーメッセージをセッションに保存して店舗詳細ページに渡す
    $_SESSION['error_message'] = "エラー: お気に入り状態の確認に失敗しました。";
}

// --- リダイレクト ---
$redirectUrl = 'store_detail.php?place_id=' . urlencode($placeId);
if (!empty($_GET['from'])) {
    $redirectUrl .= '&from=' . urlencode($_GET['from']);
}
if (!empty($_GET['diagnosis_id'])) {
    $redirectUrl .= '&diagnosis_id=' . urlencode($_GET['diagnosis_id']);
}
echo "<!DOCTYPE html><html><head><title>Redirecting...</title>";
echo "<script>window.location.replace('" . $redirectUrl . "');</script>";
echo "</head><body>Redirecting...</body></html>";
exit();
?>
