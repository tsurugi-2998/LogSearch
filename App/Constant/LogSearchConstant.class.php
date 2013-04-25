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
    const DUMMY_GIF = '/wp-content/uploads/2013/04/dummy.gif';

    /** カスタム分類：登山スタイル */
    const CATEGORY_MOUNTENEERING_STYLE = 'mounteneering_style';
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
}