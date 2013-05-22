<?php

namespace App\Helper;

require_once ABSPATH . '/FirePHPCore/FirePHP.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/TaxonomyModel.class.php';

use \FirePHP;
use App\Model\TaxonomyModel;

/**
 * ユーティリティクラス
 * 
 * @author Yoshifumi
 *
 */
class LogSearchHelper {

    /**
     * 取得したカテゴリ配列をTaxonomyModelに詰める
     * 
     * @param unknown $categories
     * @return multitype:
     */
    public static function getTaxonomyArray($taxonomy)
    {
        
        $ret = array();
        $taxonomyList = array();
        
        $args = array(
                'taxonomy' => $taxonomy,
                'hide_empty' => 1,
                'orderby ' => 'id',
        );
        $categories = get_categories( $args );

        $firephp = FirePHP::getInstance(true);
        $firephp->group('$categories');
        $firephp->log($categories);
        $firephp->groupEnd();

        foreach ($categories as $category)
        {
            $termId = (string)($category->term_id);
            $name = $category->name;
            $parentId = (string)($category->parent);

            if(isset($taxonomyList[$termId]))
            {
                $taxonomyModel = $taxonomyList[$termId];
                $taxonomyModel->name = $name;
            } else {
                $taxonomyModel = new TaxonomyModel();
                $taxonomyModel->termId = $termId;
                $taxonomyModel->name = $name;
                $taxonomyList[$termId] = $taxonomyModel;
            }

            if($parentId == 0)
            {
                array_push($ret, $taxonomyModel);
                continue;
            }

            if(!isset($taxonomyList[$parentId]))
            {
                $parent = new TaxonomyModel();
                $parent->termId = $parentId;
                $parent->name = $name;
                $parent->children = array($taxonomyModel);
                $taxonomyList[$parentId] = $parent;
            } else {
                $parent = $taxonomyList[$parentId];
                if(isset($parent->children))
                {
                    array_push($parent->children, $taxonomyModel);
                } else {
                    $parent->children = array($taxonomyModel);
                }
            }
        }
        
        $firephp = FirePHP::getInstance(true);
        $firephp->group('$ret');
        $firephp->log($ret);
        $firephp->groupEnd();

        return $ret;
    }

    /**
     * キー：カテゴリID、値：カテゴリ名の連想配列を作成する
     *
     * @param カテゴリの配列  $categories
     * @return キー：カテゴリID、値：カテゴリ名の連想配列
     */
    public static function getCategoryMap($categories) 
    {
        $category_map = array();
        foreach ($categories as $category) {
            $category_map[$category->slug] = $category->cat_name;
        }
        return $category_map;
    }

    public static function getTaxonomyName($post, $taxonomy)
    {
        $nameArray;
        $parent = -1;
        $terms = get_the_terms(  $post -> ID ,$taxonomy );
        
        $firephp = FirePHP::getInstance(true);
        $firephp->group('$terms');
        $firephp->log($terms);
        
        foreach ($terms as $term) {
            if($parent < $term->parent)
            {
                $parent = $term->parent;
                $nameArray = array($term->name);
            } else if($parent == $term->parent)
            {
                array_push($nameArray, $term->name);
            }
        }

        $name = implode('・', $nameArray);

        return $name;
    }

    /**
     * サムネイル画像のURLを取得します
     * @param unknown $image_url
     * @return string
     */
    public static function getThumbnailURL($image_url) 
    {
        $start_pos = strpos($image_url, 'http');
        $thumbnailURL = substr($image_url, $start_pos);
        return $thumbnailURL;
    }

    /**
     * カテゴリーが選択されているか判定する
     * @param unknown $category カテゴリ
     * @return boolean 選択されていたらtrue、そうでなければfalse
     */
    public static function isCategorySelected($category)
    {
        if(!isset($category)) {
            return false;
        }
        if($category != -1 && $category != 0) {
            return true;
        } else {
            return false;
        }
    }
}