<?php
/*
Plugin Name: LogSearch
Plugin URI: http://sanjc-tc.xsrv.jp/site/
Description: LogSearch is a mounteneering log search plugin.
Author: 土方　善文
Version: 0.1.0
Author URI: http://sanjc-tc.xsrv.jp/site/
*/

require_once WP_PLUGIN_DIR . '/LogSearch/App/Controller/InitController.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Controller/LogSearchController.class.php';

use App\Controller\InitController;
use App\Controller\LogSearchController;
use App\Controller\GetPageController;

add_shortcode('LogSearch','dispatcher');

/**
 * 山行検索サービス
 * 
 * eventの値によって処理を振り分ける.
 */
function dispatcher() {

    if(isset($_POST['event']) && $_POST['event'] == 'LogSearch') {
        // 山行記録検索
        $logSearchController = new LogSearchController();
        $logSearchController->logSearch();

    }  else {
        // 初期化処理
        $initController = new InitController();
        $initController->init();
    }
}