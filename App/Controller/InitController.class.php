<?php
namespace App\Controller;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Controller/LogSearchController.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Helper/LogSearchHelper.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SearchPanelView.class.php';

use App\Constant\LogSearchConstant;
use App\Controller\LogSearchController;
use App\Helper\LogSearchHelper;
use App\Model\SearchModel;
use App\View\SearchPanelView;

/**
 * 初期画面表示処理コントローラー
 * 
 * @author Yoshifumi
 *
 */
class InitController extends LogSearchController
{

    public function execute()
    {
        $searchModel = new SearchModel();
        $searchModel->styleId = -1;
        $searchModel->regionId = -1;
        $searchModel->areaId = -1;
        if(!is_user_logged_in())
        {
            // ログインしていない場合、基本ステップのみ
            $searchModel->type = LogSearchConstant::TYPE_KIHON;
        } else {
            $searchModel->typeId = -1;
        }
        $searchModel->keyword = '';
        $searchModel->startDate = date('Y-m-d', strtotime('-1 month'));
        $searchModel->endDate = date('Y-m-d');
        $searchModel->keywordType = LogSearchConstant::KEYWORD_TYPE_CONTENTS;
        $searchModel->dateType = LogSearchConstant::DATE_TYPE_POST;
        $searchModel->paged = '1';

        $this->logSearch($searchModel);
    }

}