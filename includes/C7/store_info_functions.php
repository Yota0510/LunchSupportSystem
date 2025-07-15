<?php
/**
 * ファイル名：store_info_function.php
 * 版名：V1.1
 * 作成者：樋口 智也
 * 日付：2025.07.15
 * 機能要約：C7 店舗情報管理部の関数群。外部APIとの連携や、他コンポーネントへの情報提供を行う。
 * 対応コンポーネント：C7 店舗情報管理部
 * 対応モジュール：M3, M3.1, M3.2
 */


/**
 * findStoreId (M3.1 店舗ID検索処理)
 * 担当者：樋口 智也
 * 機能概要：渡された店舗識別子が空でなければ、それを有効なIDとしてそのまま返すシミュレーション。
 * 入力:
 * - string $storeIdentifierForIdLookup: UIなどから渡された店舗識別子
 * 出力:
 * - array: 検索結果のステータスと、見つかった店舗IDを含む連想配列
 */
function findStoreId(string $storeIdentifierForIdLookup): array
{
    $result = [];

    // 渡されたIDが空文字列でなければ、それを有効なIDとみなして返す 
    if ( ! empty($storeIdentifierForIdLookup)) {
        $result = [
            'id_search_status' => 'SUCCESS',
            'found_store_id' => $storeIdentifierForIdLookup
        ];
    } else {
        // 空のIDが渡された場合のみ、見つからないエラーとする
        $result = [
            'id_search_status' => 'NOT_FOUND',
            'found_store_id' => null
        ];
    }

    return $result; // 関数の最後で一度だけreturn 
}

/**
 * provideStoreInformation (M3 店舗情報提供処理)
 * 担当者：樋口 智也
 * 機能概要：他コンポーネントからの要求種別に応じて、適切な下位モジュールを呼び出す。
 * 入力:
 * - string $infoRequestType: 要求される情報の種類（例: 'find_store_id_by_identifier'）
 * - array $requestParameters: 要求に応じたパラメータ群
 * 出力:
 * - array: 処理結果のステータスコードと、要求された情報を含む連想配列
 */
function provideStoreInformation(string $infoRequestType, array $requestParameters): array
{
    $result = [];

    switch ($infoRequestType) { // defaultは最後に記述 
        // C5からの店舗ID検索要求を処理する
        case 'find_store_id_by_identifier':
            // パラメータ存在チェック
            if ( ! isset($requestParameters['identifier'])) {
                $result = [
                    'processing_status_code' => 'ERROR_BAD_REQUEST',
                    'requested_information' => null
                ];
            } else {
                // M3.1を呼び出す
                $findResult = findStoreId($requestParameters['identifier']);

                // M3.1の結果を整形して返す
                if ($findResult['id_search_status'] === 'SUCCESS') {
                    $result = [
                        'processing_status_code' => 'SUCCESS',
                        'requested_information' => ['store_id' => $findResult['found_store_id']]
                    ];
                } else {
                    $result = [
                        'processing_status_code' => $findResult['id_search_status'],
                        'requested_information' => null
                    ];
                }
            }
            break;

        default:
            $result = [
                'processing_status_code' => 'ERROR_UNKNOWN_REQUEST_TYPE',
                'requested_information' => null
            ];
            break;
    }

    return $result; // 関数の最後で一度だけreturn 
}

/**
 * getStoreDetailsById (M3.2 店舗詳細情報取得処理)
 * 担当者：樋口 智也
 * 機能概要：Google Places APIを使い、指定されたPlace IDの店舗詳細を取得する。
 * 入力:
 * - string $placeId: Google Place ID
 * - string $apiKey: Google Places APIキー
 * 出力:
 * - ?array: 店舗詳細情報の連想配列。失敗した場合はnull
 * エラー処理:
 * - APIリクエスト失敗時にエラーログを記録する
 */
function getStoreDetailsById(string $placeId, string $apiKey): ?array
{
    $details = null;

    if ( ! empty($placeId) && ! empty($apiKey)) {
        $endpoint = "https://maps.googleapis.com/maps/api/place/details/json";
        $params = [
            'place_id' => $placeId,
            'key' => $apiKey,
            'language' => 'ja',
            'fields' => 'name,formatted_address' // 最低限、店名と住所を取得
        ];
        $url = $endpoint . '?' . http_build_query($params);

        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (($data['status'] ?? 'ERROR') === 'OK') {
                $details = $data['result'];
            }
        } else {
            error_log("Google Places API request failed for place_id: " . $placeId);
        }
    }

    return $details; // 関数の最後で一度だけreturn 
}
