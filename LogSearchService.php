<?php
/*
Plugin Name: LogSearch
Plugin URI: http://tsurugi2998.information-travel-site.com/wordpress/
Description: LogSearch is a mounteneering log search plugin.
Author: 土方　善文
Version: 0.1.0
Author URI: http://tsurugi2998.information-travel-site.com/wordpress/
*/

require_once WP_PLUGIN_DIR . '/LogSearch/App/Controller/InitController.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Controller/LogSearchController.class.php';

use App\Controller\InitController;
use App\Controller\LogSearchController;

add_shortcode('LogSearch','dispatcher');

/**
 * 山行検索サービス
 * 
 * eventの値によって処理を振り分ける.
 */
function dispatcher() {

    $event = $_POST['event'];
    if(!isset($event)) {
        // 初期化処理
        $initController = new InitController();
        $initController->init();
        return;
    } else {
        /*
         * 絞り込み表示時の処理
        */
        $logSearchController = new LogSearchController();
        $logSearchController->logSearch();
    }
}
