<?php
namespace App\Model;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Helper/LogSearchHelper.class.php';

use App\Constant\LogSearchConstant;
use App\Helper\LogSearchHelper;
/**
 * 検索条件モデル
 *
 * @author Yoshifumi
 *
 */
class SearchModel {

    /** カテゴリー：登山スタイル */
    public $mounteneeringStyleMap;
    
    /** カテゴリー：山域 */
    public $areaMap;

    /** 登山スタイル */
    public $mounteneeringStyle;
    
    /** 山域 */
    public $area;

    /** キーワード */
    public $keyword;

    /** 開始日  */
    public $startDate;

    /** 終了日 */
    public $endDate;

    /** キーワードタイプ */
    public $keywordType;

    /** 日付タイプ */
    public $dateType;

    /** ページ番号 */
    public $paged;

    public function __construct()
    {
        $this->mounteneeringStyle = $_POST['mounteneering_style'];
        $this->area = $_POST['area'];
        $this->keyword = $_POST['keyword'];
        $this->startDate = $_POST['start_date'];
        $this->endDate = $_POST['end_date'];
        $this->keywordType = $_POST['keyword_type'];
        $this->dateType = $_POST['date_type'];
        if(!isset($_POST['paged']) || !is_numeric($_POST['paged'])) {
            // ページ数が設定されていない、または、数値でない場合
            $this->paged = 1;
        } else {
            // それ以外は数値をセット
            $this->paged = intval($_POST['paged']);
        }

        /*
         * カスタム分類を取得
        */
        $this->mounteneeringStyleMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_MOUNTENEERING_STYLE);
        $this->areaMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_AREA);

    }
}