<?php

namespace App\Helper;

/**
 * ユーティリティクラス
 * 
 * @author Yoshifumi
 *
 */
class LogSearchHelper {

    /**
     * カスタム分類を取得する
     * @param unknown $taxonomy
     */
    public static function getCategories($taxonomy)
    {

        /*
         * 登山スタイルのカスタム分類を取得
        */
        $args = array(
                'taxonomy' => $taxonomy,
                'hide_empty' => 0,
                'orderby ' => 'id',
        );
        $catlists = get_categories( $args );
        return LogSearchHelper::getCategoryMap($catlists);
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

    /**
     * カスタム分類を返す
     *
     * 検索条件と一致するカスタム分類がない場合、最も親であるカスタム分類を返す
     *
     * @param unknown $post
     * @param unknown $taxonomy
     * @param unknown $slug
     * @return unknown
     */
    public static function getTaxonomyName($post, $taxonomy, $slug) 
    {
        $name;
        $parent;
        $terms = get_the_terms(  $post -> ID ,$taxonomy );
        foreach ($terms as $term) {
            $term_name = $term->name;
            if($term->slug === $slug) {
                return $term_name;
            }

            $term_parent = $term->parent;
            if(!isset($name)) {
                $name = $term_name;
                $parent = $term_parent;
                continue;
            }

            if($term_parent < $parent) {
                $name = $term_name;
            }
        }

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

}