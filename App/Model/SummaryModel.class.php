<?php
namespace App\Model;

/**
 * 
 * @author Yoshifumi
 *
 */
class SummaryModel
{
    /** 登山スタイル */
    public $styleName;

    /** 山域 */
    public $areaName;

    /** 種別名 */
    public $typeName;

    /** 開始日  */
    public $startDate;

    /** 終了日 */
    public $endDate;

    /** 記録 */
    public $logger;

    /** サムネイル画像 */
    public $thumbnailUrl;

    /** 記事のURL */
    public $postUrl;

    /** ダミー画像 */
    public $dummyUrl;

    /** 記事タイトル */
    public $postTitle;

    /** 内容  */
    public $content;

    /** 投稿日 */
    public $postDate;

    /** メンバー */
    public $member;

    /** 一般公開するになってたらtrue */
    public $isOpen = false;
}