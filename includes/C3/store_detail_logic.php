<?php
/**
 * ファイル名：store_detail_logic.php
 * 版名：V1.1
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 概要: C3 検索処理部のM2 詳細検索主処理及び、
 * 下位モジュール（FetchStoreDetail, CheckStoreDetailStatus）。
 */

//C7 店舗情報管理部のM2~M2.2を読み込む
require_once __DIR__ . '/../C7/store_info_manager.php';

/**
 * M2 詳細検索主処理（SearchStoreDetailMain）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：UIから受け取った店舗IDから、M2.1でC7に店舗情報要求を送り店舗情報を取得し，M2.2で検索結果を判定し，UIへ返却する．
 * 引数：string $placeId
 * 返却値：array ['status' => string, 'store_name' => string, 'store_map' => string, 'store_review' => string, 'store_photo' => string, 'store_address' => string, 'store_website' => string, 'store_hours' => string]
 */
function SearchStoreDetailMain($placeId) {
    $data = FetchStoreDetail($placeId); // ← M2.1を呼ぶ

    if (!$data['is_success']) {
        return [
            'status' => 'ERROR',
            'store_name' => '',
            'store_map' => '',
            'store_review' => '',
            'store_photo' => '',
            'store_address' => '',
            'store_website' => '',
            'store_hours' => ''
        ];
    }

    $status = CheckStoreDetailStatus($data['store_map'], $data['store_review'], $data['store_photo'], $data['store_address'], $data['store_website'], $data['store_hours'], $data['is_success']); // M2.2

    return [
        'status' => $status,
        'store_name' => $data['store_name'],
        'store_map' => $data['store_map'],
        'store_review' => $data['store_review'],
        'store_photo' => $data['store_photo'],
        'store_address' => $data['store_address'],
        'store_website' => $data['store_website'],
        'store_hours' => $data['store_hours'],
    ];
}

/**
 * M2.1 店舗情報取得処理（FetchStoreDetail）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：選択された店舗をC7 店舗情報管理部に送信し，店舗情報（地図，口コミ，写真，住所，ウェブサイト，営業時間）を取得する．
 * 引数：string $placeId
 * 返却値：array ['store_name' => string, 'store_map' => string, 'store_review' => string, 'store_photo' => string, 'store_address' => string, 'store_website' => string, 'store_hours' => string, 'is_success' => bool]
 */
function FetchStoreDetail($placeId) {
    return GetStoreDetailRequestMain($placeId); // ← C7 M2 を呼び出す
}

/**
 * M2.2 店舗情報確認処理（CheckStoreDetailStatus）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：店舗情報を取得することができたかどうか，検索結果（OK，NG，ERROR）を判定する．
 * 引数：string $placeId
 * 引数：string $storeMap
 * 引数：string $storeReview
 * 引数：string $storePhoto
 * 引数：string $storeAddress
 * 引数：string $storeWebsite
 * 引数：string $storeHours
 * 引数：bool $isSuccess
 * 返却値：string "OK", "NG", or "ERROR"
 */
function CheckStoreDetailStatus($storeMap, $storeReview, $storePhoto, $storeAddress, $storeWebsite, $storeHours, $isSuccess) {
    if (!$isSuccess) return 'ERROR';
    if (empty($storeMap) && empty($storeReview) && empty($storePhoto) && empty($storeAddress) && empty($storeWebsite) && empty($storeHours)) {
        return 'NG'; // 店舗情報がない
    }

    return 'OK'; // 正常取得
}
?>
