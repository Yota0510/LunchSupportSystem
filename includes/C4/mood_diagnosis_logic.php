<?php
/**
 * ファイル名：mood_diagnosis_logic.php
 * 版名：v1.3
 * 作成者：田口 陽太
 * 日付：2025.07.08
 * 機能要約：気分診断ロジック部（診断コード生成・DB照会のみ）
 * 対応コンポーネント：C4 気分診断処理部
 * 対応モジュール：M1.1 診断情報整理処理、M1.2 診断照会処理
 */

require_once __DIR__ . '/../C8/db_mood_function.php';

/**
 * CreateDiagnosisId (M1.1 診断情報整理処理)
 * 担当者：田口 陽太
 * 機能概要：POSTリクエストからmood1〜mood4を取得し、診断IDを生成する。
 * 入力:
 * - array $post: POSTリクエストのデータ
 * 出力:
 * - string $diagnosis_id: 4文字の診断ID（例: "1100"）
 */
function CreateDiagnosisId(array $post): string {
    $mood1 = $post['mood1'] ?? '0';
    $mood2 = $post['mood2'] ?? '0';
    $mood3 = $post['mood3'] ?? '0';
    $mood4 = $post['mood4'] ?? '0';
    return $mood1 . $mood2 . $mood3 . $mood4;
}

/**
 * DiagnosisInquiry (M1.2 診断照会処理)
 * 担当者：田口 陽太
 * 機能概要：診断IDをdb_mood_functionへ渡し、
 * 店舗情報を取得する。
 * 入力:
 * - string $diagnosis_id: 4文字の診断ID（例: "1100"）
 * 出力:
 * - array $storeInfo: 店舗情報の配列（例: [['name' => 'Store A', 'address' => 'Address A'], ...]）
 */
function DiagnosisInquiry(string $diagnosis_id): array {
    try {
        return ManageStoreInfoMain($diagnosis_id);
    } catch (Throwable $e) {
        error_log('[DiagnosisInquiry] DB Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * MoodCheckMain
 * mood1〜4の配列を受け取り、診断ID生成→店舗情報取得までを一括で行うラッパー関数。
 * 入力:
 * - array $post: mood1〜4を含む配列
 * 出力:
 * - array ['diagnosis_id' => string, 'storeInfo' => array, 'error' => bool]
 */
function MoodCheckMain(array $post): array {
    $diagnosis_id = CreateDiagnosisId($post);
    $storeInfo = [];
    $error = false;
    try {
        $storeInfo = DiagnosisInquiry($diagnosis_id);
    } catch (Throwable $e) {
        error_log('[MoodCheckMain] Error: ' . $e->getMessage());
        $error = true;
    }
    return [
        'diagnosis_id' => $diagnosis_id,
        'storeInfo' => $storeInfo,
        'error' => $error
    ];
}
