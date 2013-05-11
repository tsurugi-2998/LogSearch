<?php
namespace   App\Constant;

/**
 * 定数定義クラス
 *
 * @author Yoshifumi
 *
 */
class LogSearchConstant 
{
    /** 透明画像のファイルパス */
    const DUMMY_GIF = '/wp-content/uploads/2013/05/dummy.gif';

    /** POST TYPE：山行記録 */
    const POST_TYPE_LOGS = 'logs';

    /** カスタム分類：形態 */
    const CATEGORY_STYLE = 'style';
    /** カスタム分類：山域 */
    const CATEGORY_AREA = 'area';

    /** キーワードタイプ：本文*/
    const KEYWORD_TYPE_CONTENTS = '1';
    /** キーワードタイプ：メンバー*/
    const KEYWORD_TYPE_MEMBER = '2';
    /** キーワードタイプ：記録者*/
    const KEYWORD_TYPE_LOGGER = '3';

    /** 日付タイプ：山行実施日*/
    const DATE_TYPE_RUN = '1';
    /** キーワードタイプ：投稿日*/
    const DATE_TYPE_POST = '2';

    /** 山行タイトル：文字数MAX */
    const TITLE_MAX_LENGTH = 26;
    /** 山行タイトル：カットサイズ */
    const TITLE_CUT_SIZE = 29;

    /** ページネーション：1ページの投稿数 */
    const POSTS_PER_PAGE = 10;

    /** ページネーション：最大表示ページ数 */
    const PAGE_NATION_MAX_DISPLAY = 10;

    /** ページネーション：ページ前半 */
    const PAGE_NATION_FRONT_PAGE = 5;

    /** ページネーション：ページの中央 */
    const PAGE_NATION_MIDDLE_PAGE = 6;

    /** ページネーション：ページの後半 */
    const PAGE_NATION_BACK_PAGE = 4;

    /** ポップオーバーに出力する最大文字数 */
    const CONTENT_MAX_LENGTH = 100;
}