<?php
namespace App\Model;


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
}