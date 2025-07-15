<?php
/**
 * ファイル名：db_favorite_function.php
 * 版名：V1.1
 * 作成者：樋口 智也
 * 日付：2025.07.15
 * 機能要約：C6 ユーザ情報管理部のお気に入り機能に関するDB操作関数群。
 * 対応コンポーネント：C6 ユーザ情報管理部
 * 対応モジュール：M2, M2.1, M2.2, M2.3
 */

require_once __DIR__ . '/../../config/db_config.php';

/**
 * checkFavoriteRegistrationStatus (M2.1 お気に入り状態確認処理)
 * 担当者：樋口 智也
 * 機能概要：favoritesテーブルを検索し、指定店舗がお気に入り登録されているか確認する。
 * 入力:
 * - string $userId: 確認対象のユーザーID
 * - string $storeId: 確認対象の店舗ID
 * 出力:
 * - bool: 登録されていればtrue、それ以外はfalse
 */
function checkFavoriteRegistrationStatus(string $userId, string $storeId): bool
{
    $isRegistered = false; // 結果を格納する変数

    try {
        $pdo = getDbConnection();
        $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = ? AND store_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $storeId]);

        // 検索結果が1以上であれば登録済みと判断
        if ($stmt->fetchColumn() > 0) {
            $isRegistered = true;
        }
    } catch (PDOException $e) {
        // エラーログを記録
        error_log("データベースエラー (checkFavoriteRegistrationStatus): " . $e->getMessage());
        $isRegistered = false;
    }

    return $isRegistered; // 関数の最後で一度だけreturn 
}

/**
 * registerFavoriteItem (M2.2 お気に入り登録処理)
 * 担当者：樋口 智也
 * 機能概要：favoritesテーブルに新しいレコードをINSERTし、お気に入りを登録する。
 * 入力:
 * - string $userId: 登録するユーザーID
 * - string $storeId: 登録する店舗ID
 * 出力:
 * - bool: 処理の成否
 */
function registerFavoriteItem(string $userId, string $storeId): bool
{
    $isSuccess = false;

    // 既に登録済みかチェック
    if (checkFavoriteRegistrationStatus($userId, $storeId)) {
        $isSuccess = true; // 既に登録済みの場合も成功とみなす
    } else {
        try {
            $pdo = getDbConnection();
            $sql = "INSERT INTO favorites (user_id, store_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $isSuccess = $stmt->execute([$userId, $storeId]);
        } catch (PDOException $e) {
            // エラーログを記録
            error_log("データベースエラー (registerFavoriteItem): " . $e->getMessage());
            $isSuccess = false;
        }
    }

    return $isSuccess; // 関数の最後で一度だけreturn 
}

/**
 * deregisterFavoriteItem (M2.3 お気に入り解除処理)
 * 担当者：樋口 智也
 * 機能概要：favoritesテーブルから指定レコードをDELETEし、お気に入りを解除する。
 * 入力:
 * - string $userId: 解除するユーザーID
 * - string $storeId: 解除する店舗ID
 * 出力:
 * - bool: 処理の成否
 */
function deregisterFavoriteItem(string $userId, string $storeId): bool
{
    $isSuccess = false;

    try {
        $pdo = getDbConnection();
        $sql = "DELETE FROM favorites WHERE user_id = ? AND store_id = ?";
        $stmt = $pdo->prepare($sql);
        $isSuccess = $stmt->execute([$userId, $storeId]);
    } catch (PDOException $e) {
        // エラーログを記録
        error_log("データベースエラー (deregisterFavoriteItem): " . $e->getMessage());
        $isSuccess = false;
    }

    return $isSuccess; // 関数の最後で一度だけreturn 
}

/**
 * getAllFavoritesByUserId (M2.4 全お気に入り取得処理)
 * 担当者：樋口 智也
 * 機能概要：特定ユーザーのお気に入りを全て取得する。
 * 入力:
 * - string $userId: 対象のユーザーID
 * 出力:
 * - array: 店舗IDの配列
 */

function getAllFavoritesByUserId(string $userId): array
{
    $favoriteList = [];

    try {
        $pdo = getDbConnection();
        $sql = "SELECT store_id FROM favorites WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $favoriteList = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
        // エラーログを記録
        error_log("データベースエラー (getAllFavoritesByUserId): " . $e->getMessage());
        $favoriteList = [];
    }

    return $favoriteList; // 関数の最後で一度だけreturn 
}

/**
 * controlUserFavoriteData (M2 ユーザお気に入り制御処理)
 * 担当者：樋口 智也
 * 機能概要：C5からのコマンドに基づき、お気に入り関連のDB操作を振り分ける。
 * 入力:
 * - string $operationCommand: 実行する操作コマンド
 * - string $userIdForOperation: 操作対象のユーザーID
 * - string $storeIdForOperation: 操作対象の店舗ID
 * 出力:
 * - array: 処理結果の連想配列
 */
function controlUserFavoriteData(string $operationCommand, string $userIdForOperation, string $storeIdForOperation): array
{
    $result = [];

    switch ($operationCommand) { // defaultは最後に記述 
        case 'check_status':
            $isFavorite = checkFavoriteRegistrationStatus($userIdForOperation, $storeIdForOperation);
            $result = ['status' => 'success', 'is_favorite' => $isFavorite];
            break;

        case 'register':
            $isSuccess = registerFavoriteItem($userIdForOperation, $storeIdForOperation);
            $result = ['status' => $isSuccess ? 'success' : 'error'];
            break;

        case 'deregister':
            $isSuccess = deregisterFavoriteItem($userIdForOperation, $storeIdForOperation);
            $result = ['status' => $isSuccess ? 'success' : 'error'];
            break;

        default:
            $result = ['status' => 'error', 'message' => 'Invalid operation command.'];
            break;
    }

    return $result; // 関数の最後で一度だけreturn 
}
