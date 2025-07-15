<?php
/**
 * ファイル名：favorite_function.php
 * 版名：V1.1
 * 作成者：樋口 智也
 * 日付：2025.07.15
 * 機能要約：C5 お気に入り処理部のロジックを担う関数ライブラリ
 * 対応コンポーネント：C5 お気に入り処理部
 * 対応モジュール：M1, M1.1, M1.2
 */

// このファイルが必要とする、下位のコンポーネントを読み込む 
require_once __DIR__ . '/../C6/db_favorite_function.php';
require_once __DIR__ . '/../C7/store_info_functions.php';

/**
 * getStoreId (M1.1 店舗ID取得処理)
 * 担当者：樋口 智也
 * 機能概要：C7のインターフェースを呼び出し、店舗識別子から店舗IDを取得する。
 * 入力:
 * - string $storeIdentifierFromUi: UIから渡された店舗を特定するための情報
 * 出力:
 * - array: 'status'と'store_id'を含む連想配列
 */
function getStoreId(string $storeIdentifierFromUi): array
{
    $result = provideStoreInformation('find_store_id_by_identifier', ['identifier' => $storeIdentifierFromUi]);
    $response = [];

    if ($result['processing_status_code'] === 'SUCCESS') {
        $response = ['status' => 'success', 'store_id' => $result['requested_information']['store_id']];
    } else {
        $response = ['status' => 'error_c7_failure', 'store_id' => null];
    }

    return $response; // 関数の最後で一度だけreturn 
}

/**
 * manageUserFavoriteInfo (M1.2 ユーザ情報連携処理)
 * 担当者：樋口 智也
 * 機能概要：C6の制御関数を呼び出し、DB上のお気に入り情報を操作する。
 * 入力:
 * - string $userIdForFavorite: 操作対象のユーザーID
 * - string $targetStoreId: 操作対象の店舗ID
 * - string $favoriteActionType: 実行する操作の種類 ('check_status', 'register', 'deregister')
 * 出力:
 * - array: C6からの処理結果をそのまま返す
 */
function manageUserFavoriteInfo(string $userIdForFavorite, string $targetStoreId, string $favoriteActionType): array
{
    // C6の制御関数を呼び出し、結果をそのまま返す
    $result = controlUserFavoriteData($favoriteActionType, $userIdForFavorite, $targetStoreId);

    return $result; // 関数の最後で一度だけreturn 
}

/**
 * controlFavoriteProcessing (M1 お気に入り制御処理)
 * 担当者：樋口 智也
 * 機能概要：UIからの要求に基づき、店舗ID取得(C7)とユーザ情報操作(C6)の処理フローを制御する。
 * 入力:
 * - string $requestType: 処理要求の種類 ('check_status', 'register', 'deregister')
 * - string $userId: 操作を行うユーザーのID
 * - string $storeIdentifier: 対象店舗を特定するための情報
 * 出力:
 * - array: UIへ返す最終的な処理結果の連想配列
 */
function controlFavoriteProcessing(string $requestType, string $userId, string $storeIdentifier): array
{
    $response = [];

    // 必須データのチェック
    if (empty($requestType) || empty($userId) || empty($storeIdentifier)) {
        $response = ['result_status_code' => 'E1_MISSING_DATA', 'message' => '必須データが不足しています。'];
    } else {
        // 店舗IDを取得
        $idResult = getStoreId($storeIdentifier);

        if ($idResult['status'] !== 'success') {
            $response = ['result_status_code' => 'E2_C7_FAILURE', 'message' => '店舗情報の取得に失敗しました。'];
        } else {
            $retrievedStoreId = $idResult['store_id'];
            // ユーザーのお気に入り情報を操作
            $actionResult = manageUserFavoriteInfo($userId, $retrievedStoreId, $requestType);

            // UIへの最終結果を整形
            if ($requestType === 'check_status') {
                if ($actionResult['status'] === 'success') {
                    $response = ['result_status_code' => 'SUCCESS', 'is_favorite' => $actionResult['is_favorite']];
                } else {
                    $response = ['result_status_code' => 'E3_C6_FAILURE', 'message' => 'お気に入り状態の確認に失敗しました。'];
                }
            } else {
                if ($actionResult['status'] === 'success') {
                    $successMessage = ($requestType === 'register') ? 'お気に入りに登録しました。' : 'お気に入りから解除しました。';
                    $response = ['result_status_code' => 'SUCCESS', 'message' => $successMessage];
                } else {
                    $response = ['result_status_code' => 'E3_C6_FAILURE', 'message' => 'お気に入り情報の更新に失敗しました。'];
                }
            }
        }
    }

    return $response; // 関数の最後で一度だけreturn 
}
