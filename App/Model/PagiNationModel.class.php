<?php
namespace App\Model;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once ABSPATH . '/FirePHPCore/FirePHP.class.php';

use App\Constant\LogSearchConstant;
use \FirePHP;
/**
 * ページネーションモデル
 * 
 * @author Yoshifumi
 *
 */
class PagiNationModel {

    /** 現在ページ */
    public $currentPage = 1;

    /** 先頭ページ数  */
    public $startPage = 1;

    /** 最後尾ページ数  */
    public $endPage = 1;

    /**
     * コンストラクタ
     *
     * @param unknown $paged
     * @param unknown $maxNumPages
     */
    public function __construct($paged, $maxNumPages)
    {
        $firephp = FirePHP::getInstance(true);
        $this->currentPage = $paged;
    
        // 総ページ数が1ページの場合
        if($maxNumPages <= 1)
        {
            $firephp->log('総ページ数が1ページ.');
            return;
        }
    
        // 総ページ数が10ページ以下の場合
        if($maxNumPages < LogSearchConstant::PAGE_NATION_MAX_DISPLAY)
        {
            $firephp->log('総ページ数が10ページ以下.');
            $this->startPage = 1;
            $this->endPage = $maxNumPages;
            return;
        }
    
        if($paged <= LogSearchConstant::PAGE_NATION_MIDDLE_PAGE)
        {
            $firephp->log('現在ページが6ページ以下.');
            $this->startPage = 1;
            $this->endPage = LogSearchConstant::PAGE_NATION_MAX_DISPLAY;
            return;
        } else {
            $firephp->log('現在ページが7ページ以降.');
            $this->startPage = $paged - LogSearchConstant::PAGE_NATION_FRONT_PAGE;
            $endPage = $paged + LogSearchConstant::PAGE_NATION_BACK_PAGE;
            if($endPage > $maxNumPages) {
                $this->endPage = $maxNumPages;
            } else {
                $this->endPage = $endPage;
            }
    
            return;
        }
    }

    /**
     * 前へを表示するか
     * @return boolean 表示する場合はtrue、そうでなければfalse
     */
    public function isDisplayForward()
    {
        return 1 < $this->currentPage;
    }

    /**
     * 次へを表示するか
     * @return boolean 表示する場合はtrue、そうでなければfalse
     */
    public function isDisplayNext()
    {
        return $this->currentPage < $this->endPage;
    }
}