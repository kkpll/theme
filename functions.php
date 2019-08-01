<?php

/*
 *
 * CSSとJAVASCRIPTファイルの読み込み
 *
 */

function load_css_and_js(){
    //CSS
    wp_enqueue_style( 'style.css', get_stylesheet_uri() );

    //JAVASCRIPT
    global $wp_scripts;
    $jquery = $wp_scripts->registered['jquery-core'];
    $jq_ver = $jquery->ver;
    $jq_src = $jquery->src;
    wp_deregister_script( 'jquery' );
    wp_deregister_script( 'jquery-core' );
    wp_register_script( 'jquery', false, array('jquery-core'), $jq_ver, true );
    wp_register_script( 'jquery-core', $jq_src, array(), $jq_ver, true );
}

add_action( 'wp_enqueue_scripts', 'load_css_and_js' );



/*
 *
 * アイキャッチ設定
 *
 */
add_theme_support( 'post-thumbnails' );
//set_post_thumbnail_size( 340, 240, true );

/*
 *
 * カスタムメニュー登録
 *
 */
register_nav_menus( array(
    'header' => 'ヘッダーメニュー',
    'footer' => 'フッターメニュー',
    'sidebar' => 'サイドメニュー',
) );

class SimpleMenuWalker extends Walker_Nav_Menu {

    function start_lvl( &$output, $depth ){$output .= "";}
    function end_lvl( &$output, $depth ) {$output .= "";}
    function start_el( &$output, $item, $depth, $args ) {$output .= $this->create_a_tag($item, $depth, $args);}
    function end_el( &$output, $item, $depth, $args ) {$output .= '';}

    private function create_a_tag($item, $depth, $args) {
        $href = ! empty( $item->url ) ? ' href="'.esc_attr( $item->url) .'"' : '';
        $item_output = sprintf( '<a%1$s class="field-box-item">%2$s</a>',
            $href,
            apply_filters( 'the_title', $item->title, $item->ID)
        );
        return apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

}

class SideMenuWalker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth ){$output .= "";}
    function end_lvl( &$output, $depth ) {$output .= "";}
    function start_el( &$output, $item, $depth, $args ) {
        if (in_array('menu-item-has-children', $item->classes)) {
            // 親の場合
            $indent = " ";
            $output .= "\n".'<li>';
            $output .= $this->create_a_tag($item, $depth, $args);
            $output .= "\n" . $indent . '<ul>';
        }
        else {
            // 子の場合
            $output .= '<li>';
            $output .= $this->create_a_tag($item, $depth, $args);
        }
    }
    function end_el( &$output, $item, $depth, $args ) {
        if (in_array('menu-item-has-children', $item->classes)) {
            // 親の場合
            $output .= "\n".'</li></ul></li>';
        }
        else {
            // 子の場合
            $output .= "\n".'</li>';
        }
    }

    private function create_a_tag($item, $depth, $args) {
        $href = ! empty( $item->url ) ? ' href="'.esc_attr( $item->url) .'"' : '';
        $item_output = sprintf( '<a%1$s>%2$s</a>',
            $href,
            apply_filters( 'the_title', $item->title, $item->ID)
        );
        return apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

}


/*
 *
 * ページネーション設定
 *
 */
function change_posts_per_page($query) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $taxonomies = get_taxonomies(array('_builtin'=>false));
    $my_taxonomies = array();
    foreach($taxonomies as $taxonomy){
        array_push($my_taxonomies,$taxonomy);
    }

    $categories = get_categories();
    $my_categories = array();
    foreach($categories as $category){
        array_push($my_categories,$category->slug);
    }

    $posttypes = get_post_types(array('_builtin'=>false));
    $my_posttypes = array();
    foreach($posttypes as $posttype){
        array_push($my_posttypes,$posttype);
    }

    if( $query->is_category($my_categories) || $query->is_tax($my_taxonomies) || $query->is_post_type_archive($my_posttypes)){
        $query->set( 'posts_per_page',get_option('posts_per_page'));
    }
}
add_action('pre_get_posts', 'change_posts_per_page' );


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

function get_the_post_data(){

    $return = array();

    global $post;

    $post_id = $post->ID;
    $post_type = $post->post_type;
    $post_type_obj = get_post_type_object($post_type);
    $return['post_type'] = $post_type;
    $return['post_type_name'] = $post_type_obj->labels->singular_name;
    $return['permalink'] = get_the_permalink(); //パーマリンク
    $return['title'] = $post->post_title; //タイトル
    $return['content'] = $post->post_content; //本文
    $return['thumbnail'] = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id,'my-thumbnails') : NULL; //サムネイルURL
    $return['date'] = get_the_time('Y年n月j日'); //日付
    $return['excerpt'] = $post->post_excerpt; //抜粋
    $return['category'] = array();
    $return['tag'] = array();

    //記事が持つタクソノミーをすべて取得
    if($taxs = get_post_taxonomies()){
        foreach((array)$taxs as $tax){
            //post_formatだけ取り除く
            if($tax !== "post_format"){
                $taxonomy = get_taxonomy($tax);
                $taxonomy_type = $taxonomy->hierarchical ? 'category' : 'tag';
                $return[$taxonomy_type][$tax] = array();
                $terms = get_the_terms($post_id, $tax);
                foreach ((array)$terms as $term){
                    array_push($return[$taxonomy_type][$tax], array('name' => $term->name, 'slug'=> $term->slug,'link' => get_category_link($term->term_id)));
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
 *
 *
 * @return array 関連記事 $return
 *
 *
 */
function get_the_related_posts(){

    global $post;

    $return = array();

    if (is_single()){

        $post_type = get_post_type();
        $post_type_object = get_post_type_object($post_type);

        if($post_type == "post"){
            $categories = get_the_category();
            $title = $categories[0]->cat_name;
            $related_posts = get_posts(array('category__in' => array($categories[0]->cat_ID), 'exclude' => $post->ID, 'numberposts' => -1));
        }else{
            $title = esc_html($post_type_object->labels->singular_name);
            $related_posts = get_posts(array('post_type' => $post_type, 'exclude' => $post->ID ,'numberposts' => -1));
        }
        $excerpt = get_the_excerpt();

        if($related_posts){
            $return['title'] = $title;
            $return['posts'] = array();
            foreach($related_posts as $related_post){
                array_push($return['posts'],array('title'=>$related_post->post_title,'permalink'=>get_permalink($related_post->ID),'content'=>$related_post->post_content,'excerpt'=>$related_post->post_excerpt));
            }
        }

    }elseif(is_page()){

        $related_query = new WP_Query();
        $args = $related_query->query(array(
            'post_type' => 'page',
            'nopaging'  => 'true'
        ));

        if($parent_id = $post->post_parent ){
            $target = $parent_id;
            $title = get_post($parent_id)->post_title;
        }else{
            $target = $post->ID;
            $title = $post->post_title;
        }
        $excerpt = $post->post_excerpt;

        $related_posts = get_page_children($target, $args);

        if($related_posts) {
            $return['title'] = $title;
            $return['posts'] = array();
            foreach ($related_posts as $related_post) {
                array_push($return['posts'],array('title'=>$related_post->post_title,'permalink'=>get_permalink($related_post->ID),'content'=>$related_post->post_content,'excerpt'=>$related_post->post_excerpt));
            }
        }
    }

    return $return;
}


