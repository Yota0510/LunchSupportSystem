<?php
/**
 * ファイル名：stores_data.php
 * 版名：v1.1
 * 作成者：田口 陽太
 * 日付：2025.06.15
 * 機能要約：診断店舗情報をデータベースに登録する。
 * 対応コンポーネント：C8 診断店舗情報管理部
 * 対応モジュール：
 */

require_once __DIR__ . '/../config/db_config.php';

/**
 * 初期データをデータベースに挿入する関数
 */
function initializeStores() {
    try {
        $pdo = getDbConnection(); // データベース接続

        // 初期店舗データ
        $stores = [
            ['store_name' => '店舗1', 'store_location' => '場所1', 'review' => '良い', 'diagnosis_id' => '0000'],
            ['store_name' => '店舗2', 'store_location' => '場所2', 'review' => '普通', 'diagnosis_id' => '0001'],
            ['store_name' => '店舗3', 'store_location' => '場所3', 'review' => '悪い', 'diagnosis_id' => '0010'],
            ['store_name' => '店舗4', 'store_location' => '場所4', 'review' => '良い', 'diagnosis_id' => '0011'],
            ['store_name' => '店舗5', 'store_location' => '場所5', 'review' => '良い', 'diagnosis_id' => '0100'],
            ['store_name' => '店舗6', 'store_location' => '場所6', 'review' => '普通', 'diagnosis_id' => '0101'],
            ['store_name' => '店舗7', 'store_location' => '場所7', 'review' => '悪い', 'diagnosis_id' => '0110'],
            ['store_name' => '店舗8', 'store_location' => '場所8', 'review' => '良い', 'diagnosis_id' => '0111'],
            ['store_name' => '店舗9', 'store_location' => '場所9', 'review' => '普通', 'diagnosis_id' => '1000'],
            ['store_name' => '店舗10', 'store_location' => '場所10', 'review' => '悪い', 'diagnosis_id' => '1001'],
            ['store_name' => '店舗11', 'store_location' => '場所11', 'review' => '良い', 'diagnosis_id' => '1010'],
            ['store_name' => '店舗12', 'store_location' => '場所12', 'review' => '普通', 'diagnosis_id' => '1011'],
            ['store_name' => '店舗13', 'store_location' => '場所13', 'review' => '悪い', 'diagnosis_id' => '1100'],
            ['store_name' => '店舗14', 'store_location' => '場所14', 'review' => '良い', 'diagnosis_id' => '1101'],
            ['store_name' => '店舗15', 'store_location' => '場所15', 'review' => '普通', 'diagnosis_id' => '1110'],
            ['store_name' => '店舗16', 'store_location' => '場所16', 'review' => '悪い', 'diagnosis_id' => '1111']
        ];

        // データベースに店舗データを挿入
        $stmt = $pdo->prepare("INSERT INTO stores (store_name, store_location, review, diagnosis_id) VALUES (?, ?, ?, ?)");
        
        foreach ($stores as $store) {
            $stmt->execute([$store['store_name'], $store['store_location'], $store['review'], $store['diagnosis_id']]);
        }

        echo "初期データの挿入が完了しました！";
    } catch (PDOException $e) {
        echo "エラーが発生しました: " . $e->getMessage();
    }
}

// 初期データを挿入
initializeStores();

?>
