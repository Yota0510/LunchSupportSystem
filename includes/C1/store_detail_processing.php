<?php
/**
 * ファイル名：store_detail_processing.php
 * 版名：V1.1
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 概要: C1 UI処理部のM5店舗詳細主処理（StoreDetailMain）及び，
 * 下位モジュール（RequestStoreDetail, DisplayStore）
 * を定義するファイル．
 */

//セッションを開始
session_start();
//C3 検索処理部のM2~M2.2を読み込む
require_once __DIR__ . '/../C3/store_detail_logic.php';

/**
 * M5.1 店舗情報要求処理（RequestStoreDetail）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：指定店舗IDをもとにAPIから地図と口コミを取得
 * 引数：string $placeId
 * 返却値：array ['status' => string, 'store_name' => string, 'store_map' => string, 'store_review' => string, 'store_photo' => string, 'store_address' => string, 'store_website' => string, 'store_hours' => string]
 */
function RequestStoreDetail($placeId) {
    // C3 検索処理部 M2 詳細検索主処理の関数を呼び出す
    return SearchStoreDetailMain($placeId);
}

/**
 * M5.2 店舗表示画面表示処理（DisplayStore）
 * 作成者：小泉 優
 * 日付：2025.06.25
 * 機能概要：取得した店舗情報をセッションに格納し，表示用画面にリダイレクト
 * 引数：string $placeId
 * 引数：string $storeName
 * 引数：string $storeMap
 * 引数：string $storeReview
 * 引数：string $storePhoto
 * 引数：string $storeAddress
 * 引数：string $storeWebsite
 * 引数：string $storeHours
 * 返却値：store_detail.php(W8 店舗表示画面)
 */
function DisplayStore($placeId, $storeName, $storeMap, $storeReview, $storePhoto, $storeAddress, $storeWebsite, $storeHours) {
    //取得した店舗情報をセッションに格納
    $_SESSION['store_detail'] = [
        'place_id' => $placeId,
        'store_name' => $storeName,
        'store_map' => $storeMap,
        'store_review' => $storeReview,
        'store_photo' => $storePhoto,
        'store_address' => $storeAddress,
        'store_website' => $storeWebsite,
        'store_hours' => $storeHours
    ];
    
    //表示用画面(W8 店舗表示画面)にリダイレクト
    header("Location: /store_detail.php");
    exit();
}

/**
 * M5 店舗詳細主処理（StoreDetailMain）
 * 担当者: 小泉 優
 * 日付：2025.06.25
 * 機能概要: 指定された店舗IDの詳細を取得し，画面に表示（M5.1とM5.2呼び出し）． 
 * 引数： string $placeId
 * 返却値：store_detail.php(W8 店舗表示画面)またはsearch_results.php(W6 店舗表示一覧画面)
 */
function StoreDetailMain($placeId) {
    //M5.1(C3 M2の結果)を呼び出し，resultに格納
    $result = RequestStoreDetail($placeId);

    //statusがOKなら，M5.2を呼び出す．
    if ($result['status'] === 'OK') {
        DisplayStore($placeId, $result['store_name'], $result['store_map'], $result['store_review'], $result['store_photo'], $result['store_address'], $result['store_website'], $result['store_hours']);
    
    //statusがNGなら，「店舗の詳細が見つかりませんでした（E7）」とエラーメッセージを出し，W6 店舗表示一覧画面にリダイレクト．
    } elseif ($result['status'] === 'NG') {
        $_SESSION['error_message'] = '店舗の詳細が見つかりませんでした。（E7）';
        header('Location: /search_results.php');
        exit();
    
    //statusがそれ以外なら，「通信に失敗しました。もう一度お試しください。（E5）」とエラーメッセージを出し、W6 店舗表示一覧画面にリダイレクト．
    } else {
        $_SESSION['error_message'] = '通信に失敗しました。もう一度お試しください。（E5）';
        header('Location: /search_results.php');
        exit();
    }
}
