<?php
namespace App\Controller;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Helper/LogSearchHelper.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SummaryModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/PageNationModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SearchPanelView.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/SummaryListView.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/View/PageNationView.class.php';
require_once ABSPATH . '/FirePHPCore/FirePHP.class.php';

use App\Constant\LogSearchConstant;
use App\Helper\LogSearchHelper;
use App\Model\SearchModel;
use App\Model\SummaryModel;
use App\Model\PageNationModel;
use App\View\SearchPanelView;
use App\View\SummaryListView;
use App\View\PageNationView;
use \DateTime;
use \DateInterval;
use \FirePHP;

/**
 * 山行記録検索コントローラー
 * 
 * @author Yoshifumi
 *
 */
class LogSearchController 
{
    private $firephp;

    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->firephp = FirePHP::getInstance(true);
        $this->firephp->registerErrorHandler();
    }

    /**
     * 山行記録検索
     */
    public function logSearch()
    {
        $this->firephp->log('logSearch start.');
        try {
            // POST送信データからSearchModelの取得
            $searchModel = $this->createSearchModel();

            // 検索条件作成
            $args = $this->getCondition($searchModel);

            $this->firephp->group('search condition');
            $this->firephp->log($args);
            $this->firephp->groupEnd();

            // 記事検索
            $posts = query_posts( $args );

            // 検索結果件数
            $foundPosts = $GLOBALS['wp_query']->found_posts;

            // サマリーリスト作成
            $summaryModelList = $this->getSummaryModelList($posts);
            // ページネーション作成
            $pageNationModel = $this->getPageNationModel($searchModel->paged);
            // クエリをリセット
            wp_reset_query();

            // 検索条件パネル表示
            $searchPanelView = new SearchPanelView();
            $searchPanelView->display($searchModel);

            // 一覧を表示
            $summaryListView = new SummaryListView();
            $summaryListView->display($summaryModelList, $foundPosts);

            // ページネーションを表示
            $pageNationView = new PageNationView();
            $pageNationView->display($pageNationModel);
            
            if($searchModel->dateType === LogSearchConstant::DATE_TYPE_POST)
            {
                remove_filter('posts_where', 'filter_where');
            }

        } catch (Exception $e) {
             $this->firephp->error($e);
        }
        $this->firephp->log('logSearch end.');
    }

    /**
     * SearchModelを生成する.
     * 
     * @return \LogSearch\Model\SearchModel
     */
    protected function createSearchModel() 
    {
        $this->firephp->log('createSearchModel start.');

        $searchModel = new SearchModel();
        $searchModel->mounteneeringStyle = htmlspecialchars($_POST['mounteneering_style']);
        $searchModel->area = htmlspecialchars($_POST['area']);
        $searchModel->keyword = htmlspecialchars($_POST['keyword']);
        $searchModel->startDate = htmlspecialchars($_POST['start_date']);
        $searchModel->endDate = htmlspecialchars($_POST['end_date']);
        $searchModel->keywordType = htmlspecialchars($_POST['keyword_type']);
        $searchModel->dateType = htmlspecialchars($_POST['date_type']);
        if(!isset($_POST['paged']) || !is_numeric($_POST['paged'])) {
            // ページ数が設定されていない、または、数値でない場合
            $searchModel->paged = 1;
        } else {
            // それ以外は数値をセット
            $searchModel->paged = intval($_POST['paged']);
        }
        
        /*
         * カスタム分類を取得
        */
        $searchModel->mounteneeringStyleMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_MOUNTENEERING_STYLE);
        $searchModel->areaMap = LogSearchHelper::getCategories(LogSearchConstant::CATEGORY_AREA);
        
        $this->firephp->log($searchModel, 'Created Search Model.');

        $this->firephp->log('createSearchModel end.');

        return $searchModel;
    }

    /**
     * 検索条件を取得
     * 
     * @param SearchModel $searchModel
     * @return unknown
     */
    protected function getCondition(SearchModel $searchModel) {
        $this->firephp->log('getCondition start.');

        $args = array(
                'post_type' => 'mounteneering-log',
                'posts_per_page' => LogSearchConstant::POSTS_PER_PAGE,
        );
        

        /* 検索条件:期間 */
        $args += $this->getDateCondition($searchModel);
        /* 検索条件：カテゴリ */
        $args += $this->getCategoryCondition($searchModel);
        /* 検索条件：キーワード*/
        $keywordCondition = $this->getKeywordCondition($searchModel);
        if(isset($args['meta_query']) && isset($keywordCondition['meta_query'])) {
            $this->firephp->log('既にメタクエリが存在する、かつ、キーワード検索条件がメタクエリの場合');
            // 既にメタクエリが存在する、かつ、キーワード検索条件がメタクエリの場合
            foreach ($keywordCondition['meta_query'] as $condition) {
                array_push($args['meta_query'], $condition);
            }
        } else {
            $args += $keywordCondition;
        }
        /* 検索条件：ソート */
        $args += $this->getSortCondition($searchModel);
        /* ページ番号 */
        $args['paged'] = $searchModel->paged;
        $this->firephp->log('getCondition end.');

        return $args;
    }

    /**
     * カスタム分類検索条件をセット
     * 
     * @param $searchModel SearchModel
     * @param unknown $args
     */
    protected function getCategoryCondition(SearchModel $searchModel) 
    {
        $this->firephp->log('getCategoryCondition start.');
        $args = array();
        /*
         * カスタム分類が単数か複数かで引数がまったく違うためフラグ管理する
        */
        $mounteneeringStyleFlag = $this->isCategorySelected($searchModel->mounteneeringStyle);
        $areaFlag = $this->isCategorySelected($searchModel->area);

        // 登山スタイルと山域の両方検索
        if($mounteneeringStyleFlag && $areaFlag) {
            $this->firephp->log('登山スタイルと山域の両方検索');
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
            $this->firephp->log('登山スタイルのみ検索');
            $args['mounteneering_style'] = $searchModel->mounteneeringStyle;
            // 山域のみ
        } else if($areaFlag) {
            $this->firephp->log('山域のみ検索');
            $args['area'] = $searchModel->area;
        }

        $this->firephp->log('getCategoryCondition end.');

        return $args;
    }

    /**
     * 期間検索条件をセット
     * @param unknown $args query_posts関数に渡す引数
     */
    protected function getDateCondition(SearchModel $searchModel) 
    {
        $this->firephp->log('getDateCondition start.');
        $args = array();
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
            $args['meta_query'] = array(
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
            $this->firephp->log('投稿日検索.');
            // 投稿日
            add_filter('posts_where', array($this,'filter_where'));
            $args['suppress_filters'] = false;
        }
        $this->firephp->log('getDateCondition end.');
        return $args;
    }

    public function filter_where($args)
    {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        $to = new DateTime($endDate);
        $to->add(new DateInterval('P1D'));
        $toDate = $to->format('Y-m-d');

        $where  = " AND post_date >= '$startDate' ";
        $where .= " AND post_date < '$toDate' ";
        $this->firephp->log($where , 'posts_where');

        return $where;
    }

    /**
     * キーワード検索条件
     * 
     * @param unknown $searchModel
     * @param unknown $args
     */
    protected function getKeywordCondition(SearchModel $searchModel)
    {
        $this->firephp->log('getKeywordCondition start.');
        $args = array();

        if(!isset($searchModel->keyword) || trim($searchModel->keyword) == '') {
            $this->firephp->log('キーワード入力なし.');
            return $args;
        }


        if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_CONTENTS) {
            $this->firephp->log('本文検索.');
            // 本文検索
            $args['s'] = $searchModel->keyword;
            return $args;
        } else if($searchModel->keywordType === LogSearchConstant::KEYWORD_TYPE_MEMBER) {
            $this->firephp->log('メンバー検索.');
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
            $this->firephp->log('記録者検索.');
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
        $this->firephp->log('getKeywordCondition end.');
        return $args;
    }

    /**
     * ソート条件
     * 
     * @param unknown $searchModel
     * @param unknown $args
     */
    protected function getSortCondition(SearchModel $searchModel) 
    {
        $this->firephp->log('getSortCondition start.');

        $args = array();
        if($searchModel->dateType === LogSearchConstant::DATE_TYPE_RUN) {
            $this->firephp->log('山行実施日でソート');
            // 山行実施日
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'start_date';
            $args['order'] = 'DESC';
        } else {
            $this->firephp->log('投稿日でソート');
            // それ以外は投稿日
            $args['orderby'] = 'post_date';
            $args['order'] = 'DESC';
        }

        $this->firephp->log('getSortCondition end.');
        return $args;
    }

    /**
     * 検索結果からサマリーのリストを取得する.
     * 
     * @param unknown $posts
     * @return multitype:
     */
    protected function getSummaryModelList($posts) {
        $this->firephp->log('getSummaryModelList start.');
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
        $this->firephp->log('getSummaryModelList end.');
        return $summaryModelList;
    }

    /**
     * ページネーションモデル作成
     */
    protected function getPageNationModel($paged)
    {
        $this->firephp->log('getPageNationModel start.');
        $pageNationModel = new PageNationModel();
        $pageNationModel->currentPage = $paged;
        $maxNumPages = $GLOBALS['wp_query']->max_num_pages;

        // 総ページ数が1ページの場合
        if($maxNumPages <= 1)
        {
            $this->firephp->log('総ページ数が1ページ.');
            return $pageNationModel;
        }

        // 総ページ数が10ページ以下の場合
        if($maxNumPages < LogSearchConstant::PAGE_NATION_MAX_DISPLAY)
        {
            $this->firephp->log('総ページ数が10ページ以下.');
            $pageNationModel->startPage = 1;
            $pageNationModel->endPage = $maxNumPages;
            return $pageNationModel;
        }

        if($paged <= LogSearchConstant::PAGE_NATION_MIDDLE_PAGE)
        {
            $this->firephp->log('現在ページが6ページ以下.');
            $pageNationModel->startPage = 1;
            $pageNationModel->endPage = LogSearchConstant::PAGE_NATION_MAX_DISPLAY;
            return $pageNationModel;
        } else {
            $this->firephp->log('現在ページが7ページ以降.');
            $pageNationModel->startPage = $paged - LogSearchConstant::PAGE_NATION_FRONT_PAGE;
            $endPage = $paged + LogSearchConstant::PAGE_NATION_BACK_PAGE;
            if($endPage > $maxNumPages) {
                $pageNationModel->endPage = $maxNumPages;
            } else {
                $pageNationModel->endPage = $endPage;
            }

            return $pageNationModel;
        }
    }

    /**
     * カテゴリーが選択されているか判定する
     * @param unknown $category カテゴリ
     * @return boolean 選択されていたらtrue、そうでなければfalse
     */
    protected function isCategorySelected($category)
    {
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