<?php
/**
 * ファイル名：db_mood_function.php
 * 版名：v1.2
 * 作成者：田口 陽太
 * 日付：2025.07.08
 * 機能要約：診断コードに基づいて店舗情報を取得する関数を定義
 * 対応コンポーネント：C8 診断店舗情報管理部
 * 対応モジュール：M1 診断店舗情報管理主処理, M1.2 診断照会処理
 */

// データベース接続関数を読み込む
require_once __DIR__ . '/../../config/db_config.php';

/**
 * 診断コードに基づいて店舗情報を取得する
 * 
 * @param string $diagnosis_id 診断コード
 * @return array 店舗情報の配列
 */

/**
 * getStoreInfoByDiagnosisId (M1.2 診断店舗情報検索処理)
 * 担当者：田口 陽太
 * 機能概要：診断IDをdb_mood_functionへ渡し、
 * 店舗情報を取得する。
 * 入力:
 * - string $diagnosis_id: 4文字の診断ID（例: "1100"）
 * 出力:
 * - array $stores: 店舗情報の配列（例: [['store_id' => 1, 'store_name' => 'Store A', ...]）
 * エラー処理:
 * - PDOException: データベース接続エラー
 * - Exception: その他のアプリケーションエラー
 */
function GetStoreInfoByDiagnosisId(string $diagnosis_id): array {
    try {
        $pdo = getDbConnection(); // データベース接続を取得
        
        $stmt = $pdo->prepare("SELECT store_id, store_name, store_location, comment, url FROM stores WHERE diagnosis_id = ?");
        $stmt->execute([$diagnosis_id]);
        
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stores;
    } catch (PDOException $e) {
        error_log("データベースエラー (GetStoreInfoByDiagnosisId): " . $e->getMessage());
        return [];
    } catch (Exception $e) {
        error_log("アプリケーションエラー (GetStoreInfoByDiagnosisId): " . $e->getMessage());
        return [];
    }
}


/**
 * ManageStoreInfoMain (M1 診断店舗情報管理主処理)
 * 担当者：田口 陽太
 * 機能概要：診断IDを引数として受け取り，M1.1でデータベース接読を行い，
 * M1.2で該当店舗情報を検索し，それを診断結果としてC4気分診断処理部へ返す．
 * 入力:
 * - string $diagnosis_id: 4文字の診断ID（例: "1100"）
 * 出力:
 * - array $stores: 店舗情報の配列（例: [['store_id' => 1, 'store_name' => 'Store A', ...]）
 */
function ManageStoreInfoMain(string $diagnosis_id): array {
    // getDbConnectionはGetStoreInfoByDiagnosisId内で呼ばれるため、ここでは直接呼ばない
    return GetStoreInfoByDiagnosisId($diagnosis_id);
}

?>
