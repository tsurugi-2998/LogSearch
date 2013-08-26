<?php
namespace App\Controller;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SummaryModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/PagiNationModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SearchPanelView.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SummaryListView.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/PagiNationView.class.php';
require_once ABSPATH . '/FirePHPCore/FirePHP.class.php';

use App\Constant\LogSearchConstant;
use App\Helper\LogSearchHelper;
use App\Model\SearchModel;
use App\Model\SummaryModel;
use App\Model\PagiNationModel;
use App\View\SearchPanelView;
use App\View\SummaryListView;
use App\View\PagiNationView;
use \DateTime;
use \DateInterval;
use \FirePHP;
use \WP_Query;

/**
 * 山行記録検索コントローラー
 * 
 * @author Yoshifumi
 *
 */
class LogSearchController 
{
    protected $firephp;

    public function __construct() {
        $this->firephp = FirePHP::getInstance(true);
        $this->firephp->registerErrorHandler();
    }

    public function execute()
    {
        // POST送信データからSearchModelの取得
        $searchModel = new SearchModel();

        // 山行記録検索
        $this->logSearch($searchModel);
    }

    /**
     * 山行記録検索
     */
    public function logSearch(SearchModel $searchModel)
    {
        $this->firephp->log('logSearch start.');
        try {

            // 検索条件作成
            $query = $this->getCondition($searchModel);

            $this->firephp->group('search condition');
            $this->firephp->log($query);
            $this->firephp->groupEnd();

            // 記事検索
             $wp_query = new WP_Query();
             $posts = $wp_query->query($query);

            $this->firephp->group('result');
            $this->firephp->log($posts);
            $this->firephp->groupEnd();

            // 検索結果件数
            $foundPosts = $wp_query->found_posts;

            // サマリーリスト作成
            $summaryModelList = $this->getSummaryModelList($posts, $searchModel);
            // ページネーション作成
            $maxNumPages = $wp_query->max_num_pages;
            $pagiNationModel = new PagiNationModel($searchModel->paged, $maxNumPages);

            // 検索条件パネル表示
            $searchPanelView = new SearchPanelView();
            $searchPanelView->display($searchModel);

            // 一覧を表示
            $summaryListView = new SummaryListView();
            $summaryListView->display($summaryModelList, $foundPosts);

            // ページネーションを表示
            $pagiNationView = new PagiNationView();
            $pagiNationView->display($pagiNationModel);
            
            if($searchModel->dateType == LogSearchConstant::DATE_TYPE_POST)
            {
                $this->firephp->log('posts_whereをremoveします.');
                remove_filter('posts_where', 'filter_where');
            }

        } catch (Exception $e) {
             $this->firephp->error($e);
        }
        $this->firephp->log('logSearch end.');
    }

    /**
     * 検索条件を取得
     * 
     * @param SearchModel $searchModel
     * @return unknown
     */
    protected function getCondition(SearchModel $searchModel) {
        $this->firephp->log('getCondition start.');

        $query = array(
                'post_type' => LogSearchConstant::POST_TYPE_LOG,
                'posts_per_page' => LogSearchConstant::POSTS_PER_PAGE,
        );

        /* 検索条件：カテゴリ */
        $query += $this->getCategoryCondition($searchModel);
        /* 検索条件:期間 */
        $query += $this->getDateCondition($searchModel);
        /* 検索条件：キーワード*/
        $keywordCondition = $this->getKeywordCondition($searchModel);
        if(isset($query['meta_query']) && isset($keywordCondition['meta_query'])) {
            $this->firephp->log('既にメタクエリが存在する、かつ、キーワード検索条件がメタクエリの場合');
            // 既にメタクエリが存在する、かつ、キーワード検索条件がメタクエリの場合
            foreach ($keywordCondition['meta_query'] as $condition) {
                array_push($query['meta_query'], $condition);
            }
        } else {
            $query += $keywordCondition;
        }
        /* 検索条件：ソート */
        $query += $this->getSortCondition($searchModel);
        /* アクセス権限 一般公開 */
//         $openCondition = $this->getOpenCondition();
//         if(isset($query['meta_query']) && isset($openCondition['meta_query'])) 
//         {
//             foreach ($openCondition['meta_query'] as $condition) {
//                 array_push($query['meta_query'], $condition);
//             }
//         } else {
//             $query += $openCondition;
//         }

        /* ページ番号 */
        $query['paged'] = $searchModel->paged;
        $this->firephp->log('getCondition end.');

        return $query;
    }

    /**
     * カスタム分類検索条件をセット
     * 
     * @param $searchModel SearchModel
     * @param unknown $query
     */
    protected function getCategoryCondition(SearchModel $searchModel) 
    {
        $this->firephp->log('getCategoryCondition start.');
        $this->firephp->log($searchModel);
        $query = array();
        /*
         * カスタム分類が単数か複数かで引数がまったく違うためフラグ管理する
        */
        $query['tax_query'] =
        array(
                'relation' => 'AND',
        );

        if(LogSearchHelper::isCategorySelected($searchModel->styleId))
        {
            $styleArray = array(
                    'taxonomy' => 'style',
                    'field' => 'id',
                    'terms' => array($searchModel->styleId),
            );
            array_push($query['tax_query'], $styleArray);
        }


        if(LogSearchHelper::isCategorySelected($searchModel->regionId))
        {
            $areaId;
            if(LogSearchHelper::isCategorySelected($searchModel->areaId))
            {
               $areaId =  $searchModel->areaId;
            } else {
               $areaId = $searchModel->regionId;
            }

            $areaArray = array(
                    'taxonomy' => 'area',
                    'field' => 'id',
                    'terms' => array($areaId),
            );
            array_push($query['tax_query'], $areaArray);
        }


        if(LogSearchHelper::isCategorySelected($searchModel->typeId))
        {
            $typeArray = array(
                    'taxonomy' => 'type',
                    'field' => 'id',
                    'terms' => array($searchModel->typeId),
            );
            
            array_push($query['tax_query'], $typeArray);
        }

        $this->firephp->log('getCategoryCondition end.');

        return $query;
    }

    /**
     * 期間検索条件をセット
     * @param unknown $query query_posts関数に渡す引数
     */
    protected function getDateCondition(SearchModel $searchModel) 
    {
        $this->firephp->log('getDateCondition start.');
        $query = array();
        /*
         * 検索条件toの計算
        */
        $fromDate = $searchModel->startDate;
        $to = new DateTime($searchModel->endDate);
        $to->add(new DateInterval('P1D'));
        $toDate = $to->format('Y-m-d');
        /*
         * 期間検索
        */
        if($searchModel->dateType === LogSearchConstant::DATE_TYPE_RUN) {
            $this->firephp->log('山行実施日検索.');
            // 山行実施日
            $query['meta_query'] = array(
                    array(
                            'key' => 'start_date',
                            'value' => $fromDate,
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
            add_filter('posts_where', array($this, 'filter_where'));
        }
        $this->firephp->log('getDateCondition end.');
        return $query;
    }

    /**
     * キーワード検索条件
     * 
     * @param unknown $searchModel
     * @param unknown $query
     */
    protected function getKeywordCondition(SearchModel $searchModel)
    {
        $this->firephp->log('getKeywordCondition start.');
        $query = array();

        if(!isset($searchModel->keyword) || trim($searchModel->keyword) == '') {
            $this->firephp->log('キーワード入力なし.');
            return $query;
        }


        if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_CONTENTS) {
            $this->firephp->log('本文検索.');
            // 本文検索
            $query['s'] = $searchModel->keyword;
            return $query;
        } else if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_MEMBER) {
            $this->firephp->log('メンバー検索.');
            // メンバー
            $query['meta_query'] = array(
                    array(
                            'key' => 'member',
                            'value' => $searchModel->keyword,
                            'compare' => 'LIKE',
                            'type'=>'CHAR',
                    )
            );
            
        } else if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_LOGGER) {
            $this->firephp->log('山行名検索.');
            // 記録者
            $query['meta_query'] = array(
                    array(
                            'key' => 'logger',
                            'value' => $searchModel->keyword,
                            'compare' => 'LIKE',
                            'type'=>'CHAR',
                    )
            );
        }
        $this->firephp->log('getKeywordCondition end.');
        return $query;
    }

    /**
     * ログインしていない場合、一般公開する山行記録のみ取得する.
     * 
     * @return multitype:boolean
     */
    protected function getOpenCondition()
    {
        $query = array();
        if(!is_user_logged_in())
        {
            $this->firephp->log('ログインしていないユーザーなので一般公開記事のみ取得します.');
            $query['meta_query'] = array(
                    array(
                            'key' => 'open',
                            'value' => 'true',
                            'compare' => '=',
                            'type'=>'CHAR',
                    )
            );
        }
        
        return $query;
    }
    /**
     * ソート条件
     * 
     * @param unknown $searchModel
     * @param unknown $query
     */
    protected function getSortCondition(SearchModel $searchModel) 
    {
        $this->firephp->log('getSortCondition start.');

        $query = array();
//        if($searchModel->dateType === LogSearchConstant::DATE_TYPE_RUN) {
            $this->firephp->log('山行実施日でソート');
            // 山行実施日
            $query['orderby'] = 'meta_value';
            $query['meta_key'] = 'start_date';
            $query['order'] = 'DESC';
//         } else {
//             $this->firephp->log('投稿日でソート');
//             // それ以外は投稿日
//             $query['orderby'] = 'post_date';
//             $query['order'] = 'DESC';
//         }

        $this->firephp->log('getSortCondition end.');
        return $query;
    }

    public function filter_where($where = '')
    {
        $fromDate;
        $toDate;

        if(isset($_POST['start_date']))
        {
            $fromDate = $_POST['start_date'];
        } else {
            $fromDate = date('Y-m-d', strtotime('-1 year'));
            // $fromDate = date('Y-01-01');
        }
        
        if(isset($_POST['end_date']))
        {
            $to = new DateTime($_POST['end_date']);
            $to->add(new DateInterval('P1D'));
            $toDate = $to->format('Y-m-d');
        } else {
            $toDate = date('Y-m-d', strtotime('+1 day'));
        }


    
        $where .= " AND post_date >= '$fromDate'" . " AND post_date <= '$toDate' ";
        return $where;
    }

    /**
     * 検索結果からサマリーのリストを取得する.
     * 
     * @param unknown $posts
     * @return multitype:
     */
    protected function getSummaryModelList($posts, SearchModel $searchModel) {
        $this->firephp->log('getSummaryModelList start.');
        $summaryModelList;
        $summaryModelList = array();
        foreach ($posts as $post) {
            /*
             * カスタムフィールドを取得
            */

            $customFields = get_post_custom($post->ID);

            $this->firephp->group('$customFields');
            $this->firephp->log($customFields);
            $this->firephp->groupEnd();

            $this->firephp->group('Custom Fields.');
            $this->firephp->log($customFields);
            $this->firephp->groupEnd();
            $thumbnail = $customFields['thumbnail'][0];
            $leader = $customFields['leader'][0];
            $logger = $customFields['logger'][0];
            $member = $customFields['member'][0];
            $startDate = $customFields['start_date'][0];
            $endDate = $customFields['end_date'][0];
            $postDate = substr($post->post_date, 0, 10);
            $open = $customFields['open'][0];
            /*
             * カスタム分類の取得
            */
            $logSearchHelper = new LogSearchHelper();
            $styleName = LogSearchHelper::getTaxonomyName($post, 'style');
            $areaName = LogSearchHelper::getTaxonomyName($post, 'area');
            $typeName = LogSearchHelper::getTaxonomyName($post, 'type');

            /*
             * 基本的な投稿データの取得
            */
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
            $summaryModel->styleName = $styleName;
            $summaryModel->areaName = $areaName;
            $summaryModel->typeName = $typeName;
            $summaryModel->startDate = $startDate;
            $summaryModel->endDate = $endDate;
            $summaryModel->leader = $leader;
            $summaryModel->logger = $logger;
            $summaryModel->thumbnailUrl = $thumbnailUrl;
            $summaryModel->postUrl =$postUrl;
            $summaryModel->dummyUrl = $dummyUrl;
            $summaryModel->postTitle = $postTitle;
            $summaryModel->content = mb_substr(strip_tags($post->post_content), 0, LogSearchConstant::CONTENT_MAX_LENGTH) . '・・・';
            $summaryModel->member = $member;
            $summaryModel->postDate = $postDate;
            $summaryModel->isOpen = $open == 'true' ? true : false;
            
            array_push($summaryModelList, $summaryModel);
        }
        $this->firephp->log('getSummaryModelList end.');
        return $summaryModelList;
    }

    private function isMultiple($flags)
    {
        $ret = false;
        $count = 0;
        foreach ($flags as $flag)
        {
            if($flag)
            {
                $count++;
            }
        }
        
        if($count > 1)
        {
            $ret = true;
        }
        
        return $ret;
    }
}