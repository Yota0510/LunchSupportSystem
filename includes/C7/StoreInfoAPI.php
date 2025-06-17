<?php
/**
 * C7_StoreInfoManagement/StoreInfoAPI.php
 * 版名：V1.0
 * 作成者：鈴木 馨
 * 日付：2025.06.15
 * 概要: C7 店舗情報管理部のモジュールを定義するファイル。
 * Google Places APIとの具体的な連携（URL生成、API呼び出し、レスポンスの整形）を担当する。
 */

// Google Places APIキーを定義
define('GOOGLE_PLACES_API_KEY', 'AIzaSyDZ2e4P7njfO8tIKbAdp3_2WYZIpJH3bSo'); 

// 検索の中心地点（芝浦工業大学豊洲キャンパスの緯度経度）
define('DEFAULT_SEARCH_LOCATION_LAT', 35.6606); // 緯度
define('DEFAULT_SEARCH_LOCATION_LNG', 139.7945); // 経度

// Google Places APIのエンドポイントURL
define('PLACES_API_TEXTSEARCH_ENDPOINT', 'https://maps.googleapis.com/maps/api/place/textsearch/json');

/**
 * C7 M1.1 APIリクエストURL生成処理 (BuildPlaceAPIRequestURL)
 * 担当者: 鈴木 馨
 * 機能概要: 検索条件（ジャンル・金額・距離）をもとに、Google Places APIリクエストURLを生成する。
 *
 * 引数： string $genre 検索するジャンル。
 * 引数： int $distance 検索する半径距離 (メートル)。
 * 引数： int $price 検索する一人あたりの金額上限 (円)。
 * 返却値： string 生成されたAPIリクエストURL。
 */
function BuildPlaceAPIRequestURL(string $genre, int $distance, int $price): string
{
    $query_parts = [];
    if (!empty($genre)) {
        $query_parts[] = $genre;
    }
    $query_parts[] = "ランチ"; 

    if ($price > 0) {
        if ($price >= 5000) {
            $query_parts[] = "5000円以上"; 
        } else {
            $query_parts[] = "~" . $price . "円";
        }
    }
    $search_query = implode(' ', $query_parts);

    $params = [
        'query' => $search_query,
        'location' => DEFAULT_SEARCH_LOCATION_LAT . ',' . DEFAULT_SEARCH_LOCATION_LNG, 
        'radius' => $distance,
        'key' => GOOGLE_PLACES_API_KEY,
        'language' => 'ja'
    ];

    return PLACES_API_TEXTSEARCH_ENDPOINT . '?' . http_build_query($params);
}

/**
 * C7 M1.2 API検索要求処理 (RequestPlaceAPI)
 * 担当者: 鈴木 馨
 * 機能概要: M1.1で生成したURLを使ってGoogle Places API にHTTP GETリクエストを送り、レスポンスを取得する。
 *
 * 引数： string $request_url APIリクエストURLの文字列。
 * 返却値： array 以下のキーを含む結果配列:
 * - 'raw_response': array|null Places APIの生データ。通信失敗時はnull。
 * - 'success': bool 通信が成功したら true、失敗したら false。
 */
function RequestPlaceAPI(string $request_url): array
{
    $response = @file_get_contents($request_url);

    if ($response === false) {
        return ['raw_response' => null, 'success' => false]; // E1: 通信失敗
    } else {
        return ['raw_response' => json_decode($response, true), 'success' => true];
    }
}

/**
 * C7 M1.3 店舗一覧生成処理 (FormatAPIResponse)
 * 担当者: 鈴木 馨
 * 機能概要: Google Place API から取得した JSON データから，店舗名・ジャンル・距離・評価など
 * 必要な情報のみを抽出し，評価降順でソートと芝浦工業大学豊洲キャンパスとの距離を計算し，
 * 成形したリストを生成して返却する．
 *
 * 引数： array|null $raw_response Places APIの検索結果の生データ (連想配列)。
 * 引数： bool $success M1.2での通信が成功していたか。
 * 引数： int $max_distance 選択した距離
 * 返却値： array 以下のキーを含む結果配列:
 * - 'SearchResult': array 抽出・整形・ソート済みの店舗リスト。
 * - 'is_success': bool 処理が成功したら true、API通信失敗時は false。
 */
function FormatAPIResponse(?array $raw_response, bool $success, int $max_distance): array
{
    if (!$success || $raw_response === null) {
        return ['SearchResult' => [], 'is_success' => false]; // E1: API通信失敗
    }

    $stores = [];
    if (isset($raw_response['results']) && is_array($raw_response['results'])) {
        foreach ($raw_response['results'] as $place) {
            $store_lat = $place['geometry']['location']['lat'] ?? 0.0;
            $store_lng = $place['geometry']['location']['lng'] ?? 0.0;
            
            // 距離を計算
            $R = 6371000; // 地球の半径（メートル）
            $dLat = deg2rad($store_lat - DEFAULT_SEARCH_LOCATION_LAT);
            $dLon = deg2rad($store_lng - DEFAULT_SEARCH_LOCATION_LNG);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad(DEFAULT_SEARCH_LOCATION_LAT)) * cos(deg2rad($store_lat)) *
                 sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $calculated_distance = $R * $c;

            // 計算した距離が指定された最大距離を超える場合はスキップ
            if ($max_distance > 0 && $calculated_distance > $max_distance) {
                continue; // この店舗は結果に含めない
            }

            $stores[] = [
                'place_id' => $place['place_id'] ?? null,
                'name' => $place['name'] ?? '名称不明',
                'rating' => $place['rating'] ?? 0.0,
                'distance' => round($calculated_distance), // メートル単位で整数に丸める
            ];
        }

        //評価降下順でソート
        usort($stores, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });

    }

    return ['SearchResult' => $stores, 'is_success' => true];
}

/**
 * C7 M1 店舗一覧取得主処理 (StoreSearchRequestMain)
 * 担当者: 鈴木 馨
 * 機能概要: 検索条件（ジャンル・金額・距離）をもとに、Google Places API に検索リクエストを送り、
 * 取得結果を整形して C3 に返却する。
 *
 * 引数： string $genre ジャンル。
 * 引数： int $price 金額上限 (円)。
 * 引数： int $distance 距離上限 (メートル)。
 * 返却値： array 以下のキーを含む結果配列:
 * - 'SearchResult': array 検索結果の店舗リスト。
 * - 'is_success': bool API通信が成功し、かつ処理が正常に完了したら true。
 * - 'error_message': string API通信失敗時のエラーメッセージ (E1)。
 */
function StoreSearchRequestMain(string $genre, int $price, int $distance): array
{
    $request_url = BuildPlaceAPIRequestURL($genre, $distance, $price);
    $api_response = RequestPlaceAPI($request_url);

    if (!$api_response['success']) {
        return [
            'SearchResult' => [],
            'is_success' => false,
            'error_message' => 'C7: Google Places APIとの通信に失敗しました。(E1)'
        ];
    }

    $formatted_results = FormatAPIResponse($api_response['raw_response'], $api_response['success'], $distance);

    if (!$formatted_results['is_success']) {
        return [
            'SearchResult' => [],
            'is_success' => false,
            'error_message' => 'C7: APIレスポンスの処理中にエラーが発生しました。(E1)'
        ];
    }

    return [
        'SearchResult' => $formatted_results['SearchResult'],
        'is_success' => true,
        'error_message' => ''
    ];
}
