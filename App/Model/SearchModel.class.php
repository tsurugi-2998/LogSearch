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
    public $styleMap;
    
    /** カテゴリー：山域 */
    public $areaMap;

    /** カテゴリー：種別 */
    public $typeMap;

    /** 登山スタイル */
    public $style;
    
    /** 山域 */
    public $area;

    /** 種別 */
    public $type;

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
        if(isset($_POST[LogSearchConstant::CATEGORY_STYLE])) {
            $this->style = $_POST[LogSearchConstant::CATEGORY_STYLE];
        }

        if(isset($_POST[LogSearchConstant::CATEGORY_AREA])){
            $this->area = $_POST[LogSearchConstant::CATEGORY_AREA];
        } 
        
        if(isset($_POST[LogSearchConstant::CATEGORY_TYPE])){
            $this->type = $_POST[LogSearchConstant::CATEGORY_TYPE];
        }

        if(isset($_POST['keyword'])) {
            $this->keyword = $_POST['keyword'];
        }

        if(isset($_POST['start_date'])) {
            $this->startDate = $_POST['start_date'];
        }

        if(isset($_POST['end_date'])) {
            $this->endDate = $_POST['end_date'];
        }

        if(isset($_POST['keyword_type'])) {
            $this->keywordType = $_POST['keyword_type'];
        }

        if(isset($_POST['date_type'])) {
            $this->dateType = $_POST['date_type'];
        }

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
        $this->styleMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_STYLE);
        $this->areaMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_AREA);
        $this->typeMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_TYPE);

    }
}