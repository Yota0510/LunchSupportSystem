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
            ['store_id' => 'ChIJLdFxRXCJGGARI5JILkEsz4A', 'store_name' => 'ぼんたぼんた 月島駅メトロピア店', 'store_location' => '〒104-0052 東京都中央区月島１丁目３−９', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJLdFxRXCJGGARI5JILkEsz4A', 'diagnosis_id' => '0000'],
            ['store_id' => 'ChIJsUc2wjKJGGARcuMDcIoau_s', 'store_name' => 'アヒポキahipoke 豊洲店 ポキボウル＆アサイーボウル専門店', 'store_location' => '〒102 東京都江東区豊洲３丁目５−３ エスティメゾン豊洲レジデンス E棠, JP 135-0061', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJsUc2wjKJGGARcuMDcIoau_s', 'diagnosis_id' => '0001'],
            ['store_id' => 'ChIJLZMeIpaJGGARzc6Cu3Ib5ZY', 'store_name' => '築地本願寺カフェ Tsumugi はなれ月島店', 'store_location' => '〒104-0052 東京都中央区月島１丁目２−９ 築地本願寺 佃島分院 1階', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJLZMeIpaJGGARzc6Cu3Ib5ZY', 'diagnosis_id' => '0010'],
            ['store_id' => 'ChIJbftvkHyJGGARqA52OHxBPiU', 'store_name' => '銀座シシリア豊洲店', 'store_location' => '〒135-8548 東京都江東区豊洲３丁目７−５', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJbftvkHyJGGARqA52OHxBPiU', 'diagnosis_id' => '0011'],
            ['store_id' => 'ChIJF_bbN5-JGGAReaHMqHsam6Y', 'store_name' => '五穀アトレ亀戸店', 'store_location' => '〒136-0071 東京都江東区亀戸５丁目１−１ アトレ亀戸 6F', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJF_bbN5-JGGAReaHMqHsam6Y', 'diagnosis_id' => '0100'],
            ['store_id' => 'ChIJvbA8JdWJGGARbLIta-OKX5I', 'store_name' => 'WE ARE THE FARM 豊洲', 'store_location' => '〒135-0061 東京都江東区豊洲２丁目２−１ アーバンドックららぽーと豊洲3 1F', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJvbA8JdWJGGARbLIta-OKX5I', 'diagnosis_id' => '0101'],
            ['store_id' => 'ChIJaW-fyQuJGGARFV_JJi3_B4U', 'store_name' => '大戸屋ごはん処 越中島店', 'store_location' => '〒135-0044 東京都江東区越中島３丁目６−１５', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJaW-fyQuJGGARFV_JJi3_B4U', 'diagnosis_id' => '0110'],
            ['store_id' => 'ChIJVYMFHXSJGGARHxWj0vc8cU0', 'store_name' => 'サロン卵と私 ららぽーと豊洲店', 'store_location' => '〒135-0061 東京都江東区豊洲２丁目４−９ 3F', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJVYMFHXSJGGARHxWj0vc8cU0', 'diagnosis_id' => '0111'],
            ['store_id' => 'ChIJq6qqvqGJGGAR69OYszbEykM', 'store_name' => 'とんかつ田 豊洲店', 'store_location' => '〒135-0061 東京都江東区豊洲３丁目２−２４ 1F 108区', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJq6qqvqGJGGAR69OYszbEykM', 'diagnosis_id' => '1000'],
            ['store_id' => 'ChIJ3eQw96OJGGARSz1EUMkEuts', 'store_name' => 'ファーストキッチン スーパービバホーム豊洲店', 'store_location' => '〒135-0061 東京都江東区豊洲３丁目４−８ ２Ｆ', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJ3eQw96OJGGARSz1EUMkEuts', 'diagnosis_id' => '1001'],
            ['store_id' => 'ChIJDT6VHKOJGGARE-C0n6J_y3o', 'store_name' => '万福食堂 豊洲駅前店', 'store_location' => '〒135-0061 東京都江東区豊洲５丁目５−１', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJDT6VHKOJGGARE-C0n6J_y3o', 'diagnosis_id' => '1010'],
            ['store_id' => 'ChIJSXj2sKOJGGARjuv6dvQt5Nc', 'store_name' => 'フォルクス 豊洲店', 'store_location' => '〒135-0061 東京都江東区豊洲３丁目３−３ 豊洲センタービルあいプラザ １Ｆ', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJSXj2sKOJGGARjuv6dvQt5Nc', 'diagnosis_id' => '1011'],
            ['store_id' => 'ChIJz7AqsXqJGGARNZ1hHloMpMY', 'store_name' => '草庵', 'store_location' => '〒104-0052 東京都中央区月島１丁目１９−２', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJz7AqsXqJGGARNZ1hHloMpMY', 'diagnosis_id' => '1100'],
            ['store_id' => 'ChIJXW69zw2JGGARpB2Mwh5BTbE', 'store_name' => 'イタリアン NAGISATEI（ナギサテイ）門前仲町', 'store_location' => '〒135-0046 東京都江東区牡丹３丁目１２−１', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJXW69zw2JGGARpB2Mwh5BTbE', 'diagnosis_id' => '1101'],
            ['store_id' => 'ChIJN3dWjhyJGGARRAbUvAnCJGI', 'store_name' => 'おぼんdeごはん ららぽーと豊洲3店', 'store_location' => '〒135-0061 東京都江東区豊洲２丁目２−１ アーバンドック ららぽーと豊洲３ Ｂ１Ｆ', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJN3dWjhyJGGARRAbUvAnCJGI', 'diagnosis_id' => '1110'],
            ['store_id' => 'ChIJj7SDde-JGGARoRJqsv8yqcE', 'store_name' => 'タイ料理 サイアムオーキッド 豊洲センタービル店', 'store_location' => '〒135-0061 東京都江東区豊洲３丁目３−３ 豊洲センタービルあいプラザ B1F', 'url' => 'http://172.21.33.209/diagnosis_store_detail.php?place_id=ChIJj7SDde-JGGARoRJqsv8yqcE', 'diagnosis_id' => '1111']
        ];

        // データベースに店舗データを挿入
        $stmt = $pdo->prepare("INSERT INTO stores (store_id, store_name, store_location, url, diagnosis_id) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($stores as $store) {
            $stmt->execute([$store['store_id'], $store['store_name'], $store['store_location'], $store['url'], $store['diagnosis_id']]);
        }

        echo "初期データの挿入が完了しました！";
    } catch (PDOException $e) {
        echo "エラーが発生しました: " . $e->getMessage();
    }
}

// 初期データを挿入
initializeStores();

?>
