<?php
/**
 * ファイル名：ui_navigation.php (C1 UI処理部)
 * 版名：V1.0
 * 担当者：鈴木 馨
 * 日付：2025.06.17
 * 機能要約: ユーザー操作に応じて適切な画面へリダイレクトする処理を提供する。
 * 対応コンポーネント: C1 UI処理部
 * 対応モジュール: M2 画面遷移主処理, M2.1 スタート画面遷移処理, M2.2 気分診断画面遷移処理,
 * M2.3 検索画面遷移処理, M2.4 店舗表示一覧画面遷移処理, M2.5 お気に入り画面遷移処理
 */

// セッションがまだ開始されていない場合に開始 (念のため)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * E5 エラー処理: 通信失敗の場合、通信エラーが起きたことを報告し、W1ログイン画面に戻る。
 *
 * @param string $errorMessage エラーメッセージ (ログ用)
 * @return void
 */
function handleNavigationError(string $errorMessage): void
{
    // エラーログに出力 (本番環境ではより詳細なロギングシステムを使用)
    error_log("Navigation Error (E5): " . $errorMessage);

    // エラーメッセージをセッションに保存するなどして、ログイン画面で表示することも可能
    $_SESSION['navigation_error'] = "画面遷移中にエラーが発生しました。再度ログインしてください。";

    // W1 ログイン画面へリダイレクト
    header('Location: login.php?error=navigation_failed');
    exit();
}

/**
 * M2.1 スタート画面遷移処理 (C1 UI処理部)
 * 担当者：樋口 智也
 * 関数名: MoveToStart
 * 機能概要: W4, W7, W8 画面から W2 スタート画面へ遷移する。
 * 引数:
 * $action (string): 遷移要求の種類 ("to_start") - 現時点では固定
 * 返却値: なし (成功時はリダイレクトし、スクリプトは終了する)
 * エラー時: handleNavigationError を呼び出す (E5)
 */
function MoveToStart(string $action): void
{
    if ($action === 'to_start') {
        header('Location: start.php');
        exit();
    } else {
        handleNavigationError("Invalid action '{$action}' for MoveToStart.");
    }
}

/**
 * M2.2 気分診断画面遷移処理 (C1 UI処理部)
 * 担当者：田口 陽太
 * 関数名: MoveToMood
 * 機能概要: W2 スタート画面から W3 気分診断画面へ遷移する。
 * 引数:
 * $action (string): 遷移要求の種類 ("to_mood") - 現時点では固定
 * 返却値: なし (成功時はリダイレクトし、スクリプトは終了する)
 * エラー時: handleNavigationError を呼び出す (E5)
 */
function MoveToMood(string $action): void
{
    if ($action === 'to_mood') {
        header('Location: mood_check.php');
        exit();
    } else {
        handleNavigationError("Invalid action '{$action}' for MoveToMood.");
    }
}

/**
 * M2.3 検索画面遷移処理 (C1 UI処理部)
 * 担当者：鈴木 馨
 * 関数名: MoveToSearch
 * 機能概要: W2 スタート画面または W7 該当店舗なし画面から W5 検索画面へ遷移する。
 * 引数:
 * $action (string): 遷移要求の種類 ("to_search") - 現時点では固定
 * 返却値: なし (成功時はリダイレクトし、スクリプトは終了する)
 * エラー時: handleNavigationError を呼び出す (E5)
 */
function MoveToSearch(string $action): void
{
    if ($action === 'to_search') {
        header('Location: search_form.php');
        exit();
    } else {
        handleNavigationError("Invalid action '{$action}' for MoveToSearch.");
    }
}

/**
 * M2.4 店舗表示一覧画面遷移処理 (C1 UI処理部)
 * 担当者：鈴木 馨
 * 関数名: MoveToStoreList
 * 機能概要: W8 店舗表示画面から W6 店舗表示一覧画面へ遷移する。
 * 引数:
 * $action (string): 遷移要求の種類 ("to_storelist") - 現時点では固定
 * 返却値: なし (成功時はリダイレクトし、スクリプトは終了する)
 * エラー時: handleNavigationError を呼び出す (E5)
 */
function MoveToStoreList(string $action): void
{
    if ($action === 'to_storelist') {
        header('Location: search_results.php'); // 仮のファイル名。W6に相当する画面
        exit();
    } else {
        handleNavigationError("Invalid action '{$action}' for MoveToStoreList.");
    }
}

/**
 * M2.5 お気に入り画面遷移処理 (C1 UI処理部)
 * 担当者：樋口 智也
 * 関数名: MoveToFavorite
 * 機能概要: W2 スタート画面から W9 お気に入り画面へ遷移する。
 * 引数:
 * $action (string): 遷移要求の種類 ("to_favorite") - 現時点では固定
 * 返却値: なし (成功時はリダイレクトし、スクリプトは終了する)
 * エラー時: handleNavigationError を呼び出す (E5)
 */
function MoveToFavorite(string $action): void
{
    if ($action === 'to_favorite') {
        header('Location: favorites.php');
        exit();
    } else {
        handleNavigationError("Invalid action '{$action}' for MoveToFavorite.");
    }
}

/**
 * M2 画面遷移主処理 (C1 UI処理部)
 * 担当者：鈴木 馨
 * 関数名: NavigationMain
 * 機能概要: ユーザー操作に応じて適切な画面へ遷移する。
 * 引数:
 * $action (string): 遷移要求の種類
 * ("to_start", "to_mood", "to_search", "to_storelist", "to_favorite")
 * 返却値: なし (成功時はリダイレクトし、スクリプトは終了する)
 * エラー時: handleNavigationError を呼び出す (E5)
 */
function NavigationMain(string $action): void
{
    switch ($action) {
        case 'to_start':
            MoveToStart($action); // M2.1 を呼び出す
            break;
        case 'to_mood':
            MoveToMood($action); // M2.2 を呼び出す
            break;
        case 'to_search':
            MoveToSearch($action); // M2.3 を呼び出す
            break;
        case 'to_storelist':
            MoveToStoreList($action); // M2.4 を呼び出す
            break;
        case 'to_favorite':
            MoveToFavorite($action); // M2.5 を呼び出す
            break;
        default:
            // 想定外のactionが渡された場合、エラー処理
            handleNavigationError("Unknown or unsupported navigation action: '{$action}'");
            break;
    }
    // ここに到達すると、エラーを示す（リダイレクトが完了していないため）
    handleNavigationError("Navigation did not complete for action: '{$action}'");
}
