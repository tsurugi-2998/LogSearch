<?php
namespace App\Controller;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Helper/LogSearchHelper.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SummaryModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SearchPanel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SummaryList.class.php';

use App\Constant\LogSearchConstant;
use App\Helper\LogSearchHelper;
use App\Model\SearchModel;
use App\Model\SummaryModel;
use App\View\SearchPanel;
use App\View\SummaryList;
use \DateTime;
use \DateInterval;

/**
 * 山行記録検索コントローラー
 * 
 * @author Yoshifumi
 *
 */
class LogSearchController 
{
    /**
     * 山行記録検索
     */
    public function logSearch()
    {
        try {
            /*
             * POST送信データからSearchModelの取得
            */
            $searchModel = $this->createSearchModel();

            /*
             * 検索条件作成
            */
            $args = array(
                    'post_type' => 'mounteneering-log',
                    'posts_per_page' => -1,
            );

            /* 検索条件：カテゴリ */
            $args += $this->getCategoryCondition($searchModel);
            /* 検索条件:期間 */
            $args += $this->getDateCondition($searchModel);
            /* 検索条件：キーワード*/
            $keywordCondition = $this->getKeywordCondition($searchModel);
            if(isset($args['meta_query']) && isset($keywordCondition['meta_query'])) {
                // 既にメタクエリが存在する、かつ、キーワード検索条件がメタクエリの場合
                foreach ($keywordCondition['meta_query'] as $condition) {
                    array_push($args['meta_query'], $condition);
                }
            } else {
                $args += $keywordCondition;
            }
            /* 検索条件：ソート */
            $args += $this->getSortCondition($searchModel);

            // デバッグ用
//             echo '<strong>デバッグログ出力</strong></br>';
//             var_dump($args);
//             echo '</br>';

            // 記事検索
            $posts = query_posts( $args );
            $summaryModelList = $this->getSummaryModelList($posts);
            wp_reset_query();

            /*
             * 検索条件パネル表示
            */
            $searchPanel = new SearchPanel();
            $searchPanel->display($searchModel);
            if(!isset($summaryModelList)) {
                print('<br/><strong>検索結果0件です</strong><br/>');
                return;
            } else {
                print('<strong>' . count($posts) . '件ヒットしました</strong><br/>');
            }

            /*
             * 一覧を表示
            */
            $summaryList = new SummaryList();
            $summaryList->display($summaryModelList);

        } catch (Exception $e) {
            echo '捕捉した例外: ',  $e->getMessage(), "\n";
        }
    }
    
    /**
     * SearchModelを生成する.
     * 
     * @return \LogSearch\Model\SearchModel
     */
    private function createSearchModel() 
    {
        $searchModel = new SearchModel();
        $searchModel->mounteneeringStyle = htmlspecialchars($_POST['mounteneering_style']);
        $searchModel->area = htmlspecialchars($_POST['area']);
        $searchModel->keyword = htmlspecialchars($_POST['keyword']);
        $searchModel->startDate = htmlspecialchars($_POST['start_date']);
        $searchModel->endDate = htmlspecialchars($_POST['end_date']);
        $searchModel->keywordType = htmlspecialchars($_POST['keyword_type']);
        $searchModel->dateType = htmlspecialchars($_POST['date_type']);
        
        /*
         * カスタム分類を取得
        */
        $searchModel->mounteneeringStyleMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_MOUNTENEERING_STYLE);
        $searchModel->areaMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_AREA);
        
        return $searchModel;
    }
    
    /**
     * カスタム分類検索条件をセット
     * 
     * @param $searchModel SearchModel
     * @param unknown $args
     */
    private function getCategoryCondition(SearchModel $searchModel) 
    {
        $args = array();
        /*
         * カスタム分類が単数か複数かで引数がまったく違うためフラグ管理する
        */
        $mounteneeringStyleFlag = $this->isCategorySelected($searchModel->mounteneeringStyle);
        $areaFlag = $this->isCategorySelected($searchModel->area);

        // 登山スタイルと山域の両方検索
        if($mounteneeringStyleFlag && $areaFlag) {
            $mounteneeringStyleArray = array(
                    'taxonomy' => 'mounteneering_style',
                    'field' => 'slug',
                    'terms' => array($searchModel->mounteneeringStyle),
            );
        
            $areaArray = array(
                    'taxonomy' => 'area',
                    'field' => 'slug',
                    'terms' => array($searchModel->area),
            );

            $args['tax_query'] =
                 array(
                    'relation' => 'AND',
                    $mounteneeringStyleArray,
                    $areaArray,
                );
            // 登山スタイルのみ
        } else if($mounteneeringStyleFlag) {
            $args['mounteneering_style'] = $searchModel->mounteneeringStyle;
            // 山域のみ
        } else if($areaFlag) {
            $args['area'] = $searchModel->area;
        }

        return $args;
    }

    /**
     * 期間検索条件をセット
     * @param unknown $args query_posts関数に渡す引数
     */
    private function getDateCondition(SearchModel $searchModel) 
    {
        $args = array();
        /*
         * 検索条件toの計算
        */
        $to = new DateTime($searchModel->endDate);
        $to->add(new DateInterval('P1D'));
        $toDate = $to->format('Y-m-d');
        
        /*
         * 期間検索
        */
        if($searchModel->dateType === LogSearchConstant::DATE_TYPE_RUN) {
            // 山行実施日
            $args['meta_query'] = array(
                    array(
                            'key' => 'start_date',
                            'value' => $searchModel->startDate,
                            'compare' => '>=',
                            'type'=>'DATE',
                    ),
                    array(
                            'key' => 'end_date',
                            'value' => $toDate,
                            'compare' => '<',
                            'type'=>'DATE',
                    ),
            );
        
        } else {
            // 投稿日
            $where = " AND post_date >= '$searchModel->startDate' AND post_date < '$toDate' ";
            //        echo $where . '<br/>';
            add_filter( 'posts_where', $where );
            $args['suppress_filters'] = true;
        }
        
        return $args;
    }

    /**
     * キーワード検索条件
     * 
     * @param unknown $searchModel
     * @param unknown $args
     */
    private function getKeywordCondition(SearchModel $searchModel)
    {

        $args = array();

        if(!isset($searchModel->keyword) || trim($searchModel->keyword) == '') {
            return $args;
        }


        if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_CONTENTS) {
            // 本文検索
            $args['s'] = $searchModel->keyword;
            return $args;
        } else if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_MEMBER) {
            // メンバー
            $args['meta_query'] = array(
                    array(
                            'key' => 'member',
                            'value' => $searchModel->keyword,
                            'compare' => 'LIKE',
                            'type'=>'CHAR',
                    )
            );
            
        } else if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_LOGGER) {
            // 記録者
            $args['meta_query'] = array(
                    array(
                            'key' => 'logger',
                            'value' => $searchModel->keyword,
                            'compare' => 'LIKE',
                            'type'=>'CHAR',
                    )
            );
        }

        return $args;
    }

    /**
     * ソート条件
     * 
     * @param unknown $searchModel
     * @param unknown $args
     */
    private function getSortCondition(SearchModel $searchModel) 
    {

        $args = array();
        if($searchModel->dateType === LogSearchConstant::DATE_TYPE_RUN) {
            // 山行実施日
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'start_date';
            $args['order'] = 'DESC';
        } else {
            // それ以外は投稿日
            $args['orderby'] = 'post_date';
            $args['order'] = 'DESC';
        }
        
        return $args;
    }

    /**
     * 検索結果からサマリーのリストを取得する.
     * 
     * @param unknown $posts
     * @return multitype:
     */
    private function getSummaryModelList($posts) {
        $summaryModelList;
        if (have_posts()) {
            $summaryModelList = array();
            foreach ($posts as $post) {
                /*
                 * カスタムフィールドを取得
                */
                $customFields = get_post_custom($post->ID);
                $thumbnail = $customFields['thumbnail'][0];
                $logger = $customFields['logger'][0];
                $member = $customFields['member'][0];
                $startDate = $customFields['start_date'][0];
                $endDate = $customFields['end_date'][0];
                /*
                 * カスタム分類の取得
                */
                $logSearchHelper = new LogSearchHelper();
                $mounteneeringStyleName = LogSearchHelper::getTaxonomyName($post, LogSearchConstant::CATEGORY_MOUNTENEERING_STYLE, $mounteneering_style);
                $areaName = LogSearchHelper::getTaxonomyName($post, LogSearchConstant::CATEGORY_AREA, $area);
        
                /*
                 * 基本的な投稿データの取得
                */
                $postDate = substr($post->post_date, 0, 10);
                $postUrl = $post->guid . $post->post_type . '=' . urlencode($post->post_title);
                $postTitle = $post->post_title;
                if(mb_strlen($postTitle) > LogSearchConstant::TITLE_MAX_LENGTH) {
                    $postTitle = mb_strimwidth($postTitle, 0, LogSearchConstant::TITLE_CUT_SIZE) . '・・・';
                }

                /*
                 * サムネイル画像の取得
                */
                $thumbnailUrl = LogSearchHelper::getThumbnailURL($thumbnail);
                $dummyUrl = null;
                if(!isset($thumbnailUrl) || $thumbnailUrl == '') {
                    $dummyUrl = site_url() . LogSearchConstant::DUMMY_GIF;
                }
        
                $summaryModel = new SummaryModel();
                $summaryModel->mounteneeringStyleName = $mounteneeringStyleName;
                $summaryModel->areaName = $areaName;
                $summaryModel->startDate = $startDate;
                $summaryModel->endDate = $endDate;
                $summaryModel->logger = $logger;
                $summaryModel->thumbnailUrl = $thumbnailUrl;
                $summaryModel->postUrl =$postUrl;
                $summaryModel->dummyUrl = $dummyUrl;
                $summaryModel->postTitle = $postTitle;
                $summaryModel->postDate = $postDate;
                array_push($summaryModelList, $summaryModel);
            }
        }
        return $summaryModelList;
    }

    /**
     * カテゴリーが選択されているか判定する
     * @param unknown $category カテゴリ
     * @return boolean 選択されていたらtrue、そうでなければfalse
     */
    private function isCategorySelected($category) {
        if(!isset($category)) {
            return false;
        }
        if($category != 'none' && $category != 'all') {
            return true;
        } else {
            return false;
        }
    }
}