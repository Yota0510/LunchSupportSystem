<?php
/**
 * C3_SearchRequestMain.php
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.06.10
 * 概要: C3 検索処理部のモジュールを定義するファイル。
 * UIからの検索条件を受け取り、C7に検索要求を出し、結果の件数を確認してUIに返す。
 */

// C7 店舗情報管理部のモジュールを読み込む
// 想定されるファイル構造に合わせてパスを修正（例: includes/C7_StoreInfoAPI.php に配置されている場合）
require_once __DIR__ . '/../C7/StoreInfoAPI.php';

/**
 * C3 M1.1 店舗一覧取得処理 (FetchStoreList)
 * 担当者: 鈴木 馨
 * 機能概要: ジャンル・金額・距離を検索条件としてC7 店舗情報管理部に送信し、店舗一覧データ（JSON）を取得する。
 *
 * 引数： string $genre ジャンル。
 * 引数： int $price 金額上限 (円)。
 * 引数： int $distance 距離上限 (メートル)。
 * 返却値： array 以下のキーを含む結果配列:
 * - 'SearchResult': array C7から取得した店舗リスト。
 * - 'is_success': bool 通信が成功していたら true を返す。
 * - 'error_message': string 通信失敗時のエラーメッセージ (E1)。
 */
function FetchStoreList(string $genre, int $price, int $distance): array
{
    // C7 店舗情報管理部 M1 店舗一覧取得主処理を呼び出す
    $c7_result = StoreSearchRequestMain($genre, $price, $distance);

    return [
        'SearchResult' => $c7_result['SearchResult'],
        'is_success' => $c7_result['is_success'],
        'error_message' => $c7_result['error_message'] ?? ''
    ];
}

/**
 * C3 M1.2 店舗一覧確認処理 (CheckSearchStatus)
 * 担当者: 鈴木 馨
 * 機能概要: 取得したJSON件数をチェックし、検索結果の状態（OK, NO_MATCH, ERROR）を判定する。
 *
 * 引数： array $search_result M1.1で取得した店舗リスト。
 * 引数： bool $is_success M1.1での通信が成功していたか。
 * 引数： string $api_error_message M1.1で発生した通信エラーメッセージ。
 * 返却値： array 以下のキーを含む結果配列:
 * - 'SearchResult': array 検索結果の店舗リスト。
 * - 'status': string "OK", "NO_MATCH", "ERROR"。
 * - 'error_message': string エラーメッセージ (E1, E2)。
 */
function CheckSearchStatus(array $search_result, bool $is_success, string $api_error_message = ''): array
{
    $status = '';
    $error_message = '';

    if (!$is_success) {
        $status = 'ERROR'; // E1: 通信失敗
        $error_message = $api_error_message;
    } elseif (empty($search_result)) {
        $status = 'NO_MATCH'; // E2: 検索結果で条件一致店舗がなかった場合
        $error_message = '検索条件に合う店舗が見つかりませんでした。(E2)';
    } else {
        $status = 'OK';
    }

    return [
        'SearchResult' => $search_result,
        'status' => $status,
        'error_message' => $error_message
    ];
}

/**
 * C3 M1 一覧検索主処理 (SearchRequestMain)
 * 担当者: 鈴木 馨
 * 機能概要: UIから受け取った検索条件（ジャンル・金額・距離）をもとに、
 * M1.1でC7に検索要求を送り店舗リストを取得し、M1.2で検索結果の件数を確認し、結果をUIへ返却する。
 *
 * 引数： string $genre ジャンル。
 * 引数： int $price 上限金額 (円)。
 * 引数： int $distance 上限距離 (メートル)。
 * 返却値： array 以下のキーを含む結果配列:
 * - 'SearchResult': JSON形式の検索結果（店舗ID、店舗名、金額、距離）。
 * - 'status': string "OK" (条件一致店舗有)、"NO_MATCH" (条件一致店舗無)、"ERROR" (通信失敗)。
 * - 'error_message': string エラーメッセージ (E1, E2)。
 */
function SearchRequestMain(string $genre, int $price, int $distance): array
{
    // M1.1 店舗一覧取得処理を呼び出す
    $fetch_list_result = FetchStoreList($genre, $price, $distance);

    // M1.2 店舗一覧確認処理を呼び出す
    $check_status_result = CheckSearchStatus(
        $fetch_list_result['SearchResult'],
        $fetch_list_result['is_success'],
        $fetch_list_result['error_message']
    );

    // error_messageは $check_status_result から取得するように修正
    return [
        'SearchResult' => $check_status_result['SearchResult'],
        'status' => $check_status_result['status'],
        'error_message' => $check_status_result['error_message']
    ];
}
