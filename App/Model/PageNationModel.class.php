<?php
namespace App\Model;

/**
 * ページネーションモデル
 * 
 * @author Yoshifumi
 *
 */
class PageNationModel {

    /** 現在ページ */
    public $currentPage = 1;

    /** 先頭ページ数  */
    public $startPage = 1;

    /** 最後尾ページ数  */
    public $endPage = 1;

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