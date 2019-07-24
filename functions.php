<?php

//アイキャッチ有効化
add_theme_support( 'post-thumbnails' );
add_image_size( 'my-thumbnails', 768, 768, true );

//カスタムメニュー登録
register_nav_menus( array(
    'header' => 'ヘッダーメニュー',
    'footer' => 'フッターメニュー',
) );

//CSSとJAVASCRIPTを読み込む
function load_css_and_javascript(){
    if(!is_admin()){
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-core');
        wp_deregister_script('jquery-migrate');
        wp_register_script('jquery', false, array('jquery-core', 'jquery-migrate'), '1.12.4', true);
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-core', '//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', array(), '1.12.4', true);
        wp_enqueue_script('jquery-migrate', '//cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.4.1/jquery-migrate.min.js', array(), '1.4.1', true);

        //ここにCSSとJAVASCRIPTを読み込む

    }
}
add_action( 'wp_enqueue_scripts', 'load_css_and_javascript' );


/**
 *
 * ページタイトル取得
 *
 * カテゴリー・タグ・タクソノミー・カスタム投稿・検索結果の一覧ページ、シングルページのページタイトルを取得。
 *
 * @return string ページタイトル $page_title
 *
 */

function get_page_title(){

    global $post;

    $post_type = $post->post_type; //投稿タイプ
    $post_title = ''; //カスタム投稿名
    $page_title = ''; //ページタイトル

    //カスタム投稿の場合
    if($post_type !== "post"){
        $obj = get_post_type_object($post_type);
        $post_title = $obj->labels->singular_name;
    }

    //カテゴリー一覧ページ
    if(is_category()){
        $tax_slug = "category";
        $id = get_query_var('cat');
        $category = get_term($id,$tax_slug);
        $page_title = $category->name;
        $parent_id = $category->parent;
        //親カテゴリーがある場合はページタイトルに含める
        if($parent_id){
            $category = get_term($parent_id,$tax_slug);
            $page_title .= "(".$category->name.")";
        }
    //タグ一覧ページ
    }else if(is_tag()){
        $tax_slug = "post_tag";
        $id = get_query_var('tag_id');
        $tag = get_term($id,$tax_slug);
        $page_title = $tag->name;
    //タクソノミー一覧ページ
    }else if(is_tax()){
        $tax_slug = get_query_var('taxonomy');
        $term_slug = get_query_var('term');
        $term = get_term_by("slug",$term_slug,$tax_slug);
        $id = $term->term_id;
        $taxonomy = get_term($id,$tax_slug);
        //カスタム投稿タイプの場合はページタイトルに投稿名を含める
        if($post_title){
            $page_title = $post_title."(".$taxonomy->name.")";
        }else{
            $page_title = $taxonomy->name;
        }
    //カスタム投稿一覧ページ
    }else if(is_post_type_archive()){
        $page_title = $post_title;
    //検索結果一覧ページ
    }else if(is_search()){
        $page_title = "「".get_search_query()."」の検索結果";
    //個別ページ
    }else if(is_single()){
        $page_title = get_the_title();
    }

    return $page_title;

}


/**
 *
 * 記事データ取得
 *
 * タイトル・コンテンツ・パーマリンク・日付・抜粋・カテゴリー・タグ・タクソノミー情報を取得
 *
 * @return array 記事データ $return
 *
 *
*/

function get_post_data(){

    $return = array(); //記事データ

    global $post;

    $post_id = $post->ID;
    $return['permalink'] = get_the_permalink(); //パーマリンク
    $return['title'] = $post->post_title; //タイトル
    $return['content'] = $post->post_content; //本文
    $return['thumbnail'] = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id,'my-thumbnails') : NULL; //サムネイルURL
    $return['date'] = get_the_time('Y年n月j日'); //日付
    $return['excerpt'] = $post->post_excerpt; //抜粋

    //記事が持つタクソノミーをすべて取得
    if($taxs = get_post_taxonomies()){
        foreach((array)$taxs as $tax){
            //post_formatだけ取り除く
            if($tax !== "post_format"){
                $return[$tax] = array();
                $terms = get_the_terms($post_id, $tax);
                foreach ((array)$terms as $term){
                    //ターム名とパーマリンクを取得
                    array_push($return[$tax], array('name' => $term->name, 'link' => get_category_link($term->term_id)));
                }
            }
        }
    }

    return $return;

}


/**
 *
 * 関連記事取得
 *
 * シングルページと固定ページで関連記事を取得
 * HTMLを返す
 *
 * @return string 関連記事 $output
 *
 *
 */
function get_related_posts(){

    global $post;

    $output = '';

    if (is_single()){
        $post_type = get_post_type();
        $post_type_object = get_post_type_object($post_type);
        if($post_type) {
            if($post_type == "post"){
                $categories = get_the_category();
                $related_post_name = $categories[0]->cat_name;
                $related_posts = get_posts(array('category__in' => array($categories[0]->cat_ID), 'exclude' => $post->ID, 'numberposts' => -1));
            }else{
                $related_post_name = esc_html($post_type_object->labels->singular_name);
                $related_posts = get_posts(array('post_type' => $post_type, 'exclude' => $post->ID ,'numberposts' => -1));
            }
        }

        if($related_posts){
            $output .= "<ul class='related-posts'>";
            $output .= "<h2 class='related-posts__title'>「".$related_post_name."」の関連記事はこちら</h2>";
            foreach($related_posts as $related_post){
                $output .= "<li><a href='".get_permalink($related_post->ID)."'>".$related_post->post_title."</a></li>";
            }
            $output .= "</ul>";
        }
    }elseif(is_page()){
        if($parent_id = $post->post_parent ){
            $children = wp_list_pages( "title_li=&child_of=".$post->post_parent."&echo=0&exclude=".$post->ID);
            $related_post_name = get_post($parent_id)->post_title;
        }else{
            $children = wp_list_pages( "title_li=&child_of=".$post->ID."&echo=0&exclude=".$post->ID );
            $related_post_name = get_the_title();
        }
        if($children){
            $output .= "<ul class='related-posts'><h2>「".$related_post_name."」の関連ページはこちら</h2>".$children."</ul>";
        }
    }

    return $output;
}