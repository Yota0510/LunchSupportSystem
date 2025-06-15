<?php
/**
 * search_processing.php
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.06.15
 * 概要: C1 UI処理部 M4店舗検索主処理のうち、
 * 下位モジュール（GetSearchCondition, SendSearchRequest, DisplayStoreList, DisplayNoResult）
 * を定義するファイル。
 */

// C3 検索処理部の主処理を読み込む
require_once __DIR__ . '/../C3/SearchRequest.php';

/**
 * M4.1 検索入力確認処理 (GetSearchCondition)
 * 担当者: 鈴木 馨
 * 機能概要: W5検索画面の入力欄から、ジャンル・金額・距離の検索条件を取得し、
 * 入力値の基本的なチェックを行う。
 * 特に、E6「ジャンル、上限金額、上限距離のいずれも未設定だった場合」のエラーを検出する。
 *
 * 引数： $input フォームからの入力データ (通常は $_GET または $_POST から渡される連想配列)
 * 返却値： array 以下のキーを含む結果配列:
 * - 'is_valid': bool  入力が有効であれば true、そうでなければ false。
 * - 'genre': string  取得したジャンル文字列 (XSS対策済み)。
 * - 'distance': int  取得した距離 (メートル)。デフォルトは0。
 * - 'price': int  取得した金額上限 (円)。デフォルトは0。
 * - 'error_message': string  入力が無効な場合のエラーメッセージ。有効な場合は空文字列。
 */
function GetSearchCondition(array $input): array
{
    $genre = isset($input['genre']) ? htmlspecialchars($input['genre']) : '';
    $distance = isset($input['distance']) ? (int)$input['distance'] : 0; // 値がセットされていない場合は0をデフォルト値
    $price = isset($input['price']) ? (int)$input['price'] : 0;     // 値がセットされていない場合は0をデフォルト値

    $is_valid = true;
    $error_message = '';

    // E6: ジャンル、上限金額、上限距離のいずれも未設定だった場合のバリデーション
    if (empty($genre) && $distance === 0 && $price === 0) {
        $is_valid = false;
        $error_message = 'ジャンル、距離、金額のいずれか一つ以上を選択してください。(E6)';
    }
    
    // 補足: Distanceのデフォルト値について
    // Places API へのリクエスト時に radius=0 だと意図しない結果になるため,最大の5000ｍをセットする
    // E6チェックでエラーではない場合のみ適用。
    // distanceが0で、かつgenreが空でない、またはpriceが0でない場合にdistanceを5000mに設定
    if ($is_valid && $distance === 0 && (!empty($genre) || $price !== 0)) {
        $distance = 5000; 
    }

    return [
        'is_valid' => $is_valid,
        'genre' => $genre,
        'distance' => $distance,
        'price' => $price,
        'error_message' => $error_message
    ];
}


/**
 * M4.2 店舗検索要求処理 (SendSearchRequest)
 * 担当者: 鈴木 馨
 * 機能概要: 検索の要求をC3 検索処理部に送り、検索結果（店舗リスト、ステータス）を取得する。
 *
 * 引数： string $genre ジャンル。
 * 引数： int $price 上限金額 (円)。
 * 引数： int $distance 上限距離 (メートル)。
 * 返却値： array 以下のキーを含む結果配列:
 * - 'SearchResult': JSON形式の検索結果（店舗ID、店舗名、金額、距離）。
 * - 'status': string "OK" (条件一致店舗有)、"NO_MATCH" (条件一致店舗無)、"ERROR" (通信失敗)。
 * - 'error_message': string エラーメッセージ (E1, E2)。
 */
function SendSearchRequest(string $genre, int $price, int $distance): array
{
    // C3 M1 一覧検索主処理 (SearchRequestMain) を呼び出す
    return SearchRequestMain($genre, $price, $distance);
}

/**
 * M4.3 店舗表示一覧画面表示処理 (DisplayStoreList)
 * 担当者: 鈴木 馨
 * 機能概要: 取得した店舗情報のリストをW6 店舗表示一覧画面へリダイレクトする。
 * この関数は直接HTMLを出力せず、search_results.phpへブラウザを転送する。
 * 検索条件はGETパラメータとしてsearch_results.phpに引き継がれる。
 *
 * 引数： array $search_results 検索結果の店舗情報配列 (この関数では直接使用しないが、引数として定義)
 * 返却値： void (リダイレクト後、処理は終了するため)
 */
function DisplayStoreList(array $search_results)
{
    // search_results.php に元の検索条件をGETパラメータとして渡し、リダイレクトする。
    header('Location: search_results.php?' . http_build_query($_GET)); 
    exit(); // リダイレクト後、スクリプトの実行を終了
}

/**
 * M4.4 該当店舗なし画面表示処理 (DisplayNoResult)
 * 担当者: 鈴木 馨
 * 機能概要: 検索結果が0件だった場合に、W7該当店舗なし画面へリダイレクトする。
 * この関数は直接HTMLを出力せず、no_results.phpへブラウザを転送する。
 *
 * 引数： array $search_result 空の検索結果 (この関数では直接使用しないが、仕様に合わせて引数に残す)
 * 返却値： void (リダイレクト後、処理は終了するため)
 */
function DisplayNoResult(array $search_result)
{
    // 該当店舗なし画面へリダイレクトする。
    header('Location: no_results.php');
    exit(); // リダイレクト後、スクリプトの実行を終了
}
