<?php
namespace App\Controller;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Helper/LogSearchHelper.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SearchPanelView.class.php';

use App\Constant\LogSearchConstant;
use App\Helper\LogSearchHelper;
use App\Model\SearchModel;
use App\View\SearchPanelView;

/**
 * 初期画面表示処理コントローラー
 * 
 * @author Yoshifumi
 *
 */
class InitController 
{

    /**
     * 初期画面表示処理
     */
    public function init() 
    {
        /*
         * カスタム分類を取得
         */
        $mounteneeringStyleMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_MOUNTENEERING_STYLE);
        $areaMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_AREA);
        /*
         * 検索条件モデル作成
         */
        $searchModel = new SearchModel();
        $searchModel->mounteneeringStyleMap = $mounteneeringStyleMap;
        $searchModel->areaMap = $areaMap;
        $searchModel->mounteneeringStyle = 'none';
        $searchModel->area = 'none';
        $searchModel->keyword = '';
        $searchModel->startDate = date('Y-m-d', strtotime('-1 month'));
        $searchModel->endDate = date('Y-m-d');
        $searchModel->keywordType = LogSearchConstant::KEYWORD_TYPE_CONTENTS;
        $searchModel->dateType = LogSearchConstant::DATE_TYPE_RUN;
        $searchModel->paged = '1';

        /*
         * 検索条件パネル表示
         */
        $searchPanelView = new SearchPanelView();
        $searchPanelView->display($searchModel);
    }
}