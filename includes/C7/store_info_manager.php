<?php
/**
 * store_info_manager.php
 * 版名：V1.1
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 概要: C7 店舗情報管理部のM2 店舗情報取得主処理及び、
 * 下位モジュール（FetchStoreDetailFromAPI, generateMapHtml, generateReviewHtml）。
 */

// Google Places APIキーを取得
require_once __DIR__ . '/../../config/api_keys.php';

/**
 * M2 店舗情報取得主処理（GetStoreDetailRequestMain）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：選択された店舗の店舗IDから，Google Place API に検索リクエストを送り，取得結果を C3 に返却する．
 * 引数：string $placeId
 * 返却値：array ['store_name'=> string, 'store_map'=> string, 'store_review' => string, 'store_photo' => string, 'store_address' => string, 'store_website' => string, 'store_hours' => string, 'is_success' => bool]
 */
function GetStoreDetailRequestMain(string $placeId): array {
    $request_url = BuildPlaceDetailRequestURL($placeId); // M2.1
    return RequestStoreDetailFromAPI($request_url);      // M2.2
}

/**
 * M2.1 APIリクエスト詳細URL生成処理（BuildPlaceDetailRequestURL）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：店舗IDを含んだ，Google Place APIリクエストURLを生成する．
 * 引数：string $placeId
 * 返却値：string $url
 */
function BuildPlaceDetailRequestURL(string $placeId): string {
    $endpoint = 'https://maps.googleapis.com/maps/api/place/details/json';
    $params = [
        'place_id' => $placeId,
        'key' => GOOGLE_PLACES_API_KEY,
        'language' => 'ja',
        'fields' => 'name,geometry,reviews,photos,formatted_address,website,opening_hours'
    ];
    return $endpoint . '?' . http_build_query($params);
}

/**
 * M2.2 API詳細検索要求処理（RequestStoreDetailFromAPI）
 * 担当者：小泉 優
 * 日付：2025.06.25
 * 機能概要：M2.1で生成したURLを使ってGoogle Place API にHTTP GETリクエストを送り，レスポンスを取得する．
 * 引数：string $url
 * 返却値：array ['store_name'=> string, 'store_map'=> string, 'store_review' => string, 'store_photo' => string, 'store_address' => string, 'store_website' => string, 'store_hours' => string, 'is_success' => bool]
 */
function RequestStoreDetailFromAPI(string $url): array {
    $response = @file_get_contents($url);
    if ($response === false) {
        return ['store_name' => '', 'store_map' => '', 'store_review' => '', 'store_photo' => '', 'store_address' => '', 'store_website' => '', 'store_hours' => '', 'is_success' => false];
    }

    $data = json_decode($response, true);
    if (($data['status'] ?? '') !== 'OK') {
        return ['store_name' => '', 'store_map' => '', 'store_review' => '', 'store_photo' => '', 'store_address' => '', 'store_website' => '', 'store_hours' => '', 'is_success' => false];
    }

    $result = $data['result'];
    //店舗名
    $store_name = $result['name'] ?? '';
    // 地図
    if (isset($result['geometry']['location']['lat'], $result['geometry']['location']['lng'])) {
        $lat = $result['geometry']['location']['lat'];
        $lng = $result['geometry']['location']['lng'];
        $map_html = '<iframe width="100%" height="300" frameborder="0" style="border:0" ' .
        'src="https://www.google.com/maps/embed/v1/view?key=' . GOOGLE_PLACES_API_KEY .
        '&center=' . $lat . ',' . $lng . '&zoom=17" allowfullscreen></iframe>';
    }

    // 口コミ
    $review_html = '<p>口コミはありません。</p>';
    if (!empty($result['reviews'])) {
        $review_html = '<h3>口コミ:</h3><ul style="list-style:none;padding-left:0;">';
        foreach ($result['reviews'] as $review) {
            $author = htmlspecialchars($review['author_name'] ?? '匿名');
            $text = nl2br(htmlspecialchars($review['text'] ?? ''));
            $rating = htmlspecialchars($review['rating'] ?? 'N/A');
            $review_html .= "<li style='margin-bottom:15px;border-bottom:1px solid #ccc;padding:10px;'>
                                <strong>{$author}</strong>（★{$rating}）<br>{$text}</li>";
        }
        $review_html .= '</ul>';
    }

    //写真
    $photo_html = '<p>写真はありません。</p>';
    if (!empty($result['photos'][0]['photo_reference'])) {
        $photo_ref = $result['photos'][0]['photo_reference'];
        $photo_url = 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=' . urlencode($photo_ref) . '&key=' . GOOGLE_PLACES_API_KEY;

        $photo_html = '<img src="' . htmlspecialchars($photo_url) . '" alt="店舗写真" style="width:100%; max-width:600px; height:auto; border-radius:8px; margin: 0 auto 20px auto; display:block;">';
    }

    // 住所
    $store_address = $result['formatted_address'] ?? '住所情報なし';

    // ウェブサイト
    $store_website = $result['website'] ?? '';

    // 営業時間
    $opening_text = '<p>営業時間情報なし</p>';
    if (!empty($result['opening_hours']['weekday_text'])) {
        $opening_text = '<h3>営業時間:</h3><ul style="padding-left: 1.5em;">';
        foreach ($result['opening_hours']['weekday_text'] as $day) {
            $opening_text .= '<li>' . htmlspecialchars($day) . '</li>';
        }
        $opening_text .= '</ul>';
    }

    return [
        'store_name' => $store_name,
        'store_map' => $map_html,
        'store_review' => $review_html,
        'store_photo' => $photo_html,
        'store_address' => $store_address,
        'store_website' => $store_website,
        'store_hours' => $opening_text,
        'is_success' => true
    ];
}
