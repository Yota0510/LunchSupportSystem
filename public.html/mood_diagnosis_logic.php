<?php
/**
 * ファイル名：mood_diagnosis_logic.php
 * 版名：v1.1
 * 作成者：田口 陽太
 * 日付：2025.06.15
 * 機能要約：mood_check.phpから受け取った気分入力([mood(1~4)])を
 * 診断コード(diagnosisCode)へ変換し、mood_result.phpへ返す。
 * 対応コンポーネント：C4 気分診断処理部
 * 対応モジュール：M1.1 診断情報整理処理、M1.2 診断照会処理
 */

require_once __DIR__ . '/../includes/F/db_functions.php'; // GetStoreInfoByDiagnosisCode() を含むファイル

// POSTで受け取った4つの気分を連結して診断コードを生成
$diagnosisCode = $_POST['mood1'] . $_POST['mood2'] . $_POST['mood3'] . $_POST['mood4'];

// 推薦店舗を取得
$recommendedStores = GetStoreInfoByDiagnosisCode($diagnosisCode);

// セッションで推薦店舗情報を渡す
session_start();
$_SESSION['recommendedStores'] = $recommendedStores;

// mood_result.phpへリダイレクト（診断コードもクエリで渡す）
header("Location: mood_result.php?code=" . $diagnosisCode);
exit;
?>
