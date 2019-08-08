<?php

/*
 *
 * CSSとJAVASCRIPTファイルの読み込み
 *
 */

function load_css_and_js(){
    //CSS
    wp_enqueue_style( 'style.css', get_stylesheet_uri(),array(), filemtime(get_stylesheet_directory()),"all");
    wp_enqueue_style( 'mobile.css', get_template_directory_uri().'/css/style-sp.css',array('style.css'),filemtime(get_template_directory().'/css/mobile.css'),'screen and (max-width:768px)');

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
            $output .= "\n" . $indent . '<ul class="sub-menu">';
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

//テキストエリアの内容を改行コードで分割して配列を返す
function get_array_from_textarea_by_br($str){
    $split = str_replace(array("\r\n", "\r", "\n"), "\n", $str);
    $arr = explode("\n", $split);
    return $arr;
}

//パンくずリスト
function breadcrumb( $wp_obj = null ) {

    // トップページでは何も出力しない
    if ( is_home() || is_front_page() ) return false;

    //そのページのWPオブジェクトを取得
    $wp_obj = $wp_obj ?: get_queried_object();

    echo '<div class="breadcrumb">'.  //id名などは任意で
        '<ul>'.
        '<li>'.
        '<a href="'. home_url() .'"><span>ホーム</span></a>'.
        '</li>';

    if ( is_attachment() ) {

        /**
         * 添付ファイルページ ( $wp_obj : WP_Post )
         * ※ 添付ファイルページでは is_single() も true になるので先に分岐
         */
        echo '<li><span>'. $wp_obj->post_title .'</span></li>';

    } elseif ( is_single() ) {

        /**
         * 投稿ページ ( $wp_obj : WP_Post )
         */
        $post_id    = $wp_obj->ID;
        $post_type  = $wp_obj->post_type;
        $post_title = $wp_obj->post_title;

        // カスタム投稿タイプかどうか
        if ( $post_type !== 'post' ) {

            $the_tax = "";  //そのサイトに合わせ、投稿タイプごとに分岐させて明示的に指定してもよい

            // 投稿タイプに紐づいたタクソノミーを取得 (投稿フォーマットは除く)
            $tax_array = get_object_taxonomies( $post_type, 'names');
            foreach ($tax_array as $tax_name) {
                if ( $tax_name !== 'post_format' ) {
                    $the_tax = $tax_name;
                    break;
                }
            }

            //カスタム投稿タイプ名の表示
            echo '<li>'.
                '<a href="'. get_post_type_archive_link( $post_type ) .'">'.
                '<span>'. get_post_type_object( $post_type )->label .'</span>'.
                '</a>'.
                '</li>';

        } else {
            $the_tax = 'category';  //通常の投稿の場合、カテゴリーを表示
        }

        // タクソノミーが紐づいていれば表示
        if ( $the_tax !== "" ) {

            $child_terms = array();   // 子を持たないタームだけを集める配列
            $parents_list = array();  // 子を持つタームだけを集める配列

            // 投稿に紐づくタームを全て取得
            $terms = get_the_terms( $post_id, $the_tax );

            if ( !empty( $terms ) ) {

                //全タームの親IDを取得
                foreach ( $terms as $term ) {
                    if ( $term->parent !== 0 ) $parents_list[] = $term->parent;
                }

                //親リストに含まれないタームのみ取得
                foreach ( $terms as $term ) {
                    if ( ! in_array( $term->term_id, $parents_list ) ) $child_terms[] = $term;
                }

                // 最下層のターム配列から一つだけ取得
                $term = $child_terms[0];

                if ( $term->parent !== 0 ) {

                    // 親タームのIDリストを取得
                    $parent_array = array_reverse( get_ancestors( $term->term_id, $the_tax ) );

                    foreach ( $parent_array as $parent_id ) {
                        $parent_term = get_term( $parent_id, $the_tax );
                        echo '<li>'.
                            '<a href="'. get_term_link( $parent_id, $the_tax ) .'">'.
                            '<span>'. $parent_term->name .'</span>'.
                            '</a>'.
                            '</li>';
                    }
                }

                // 最下層のタームを表示
                echo '<li>'.
                    '<a href="'. get_term_link( $term->term_id, $the_tax ). '">'.
                    '<span>'. $term->name .'</span>'.
                    '</a>'.
                    '</li>';
            }
        }

        // 投稿自身の表示
        echo '<li><span>'. $post_title .'</span></li>';

    } elseif ( is_page() ) {

        /**
         * 固定ページ ( $wp_obj : WP_Post )
         */
        $page_id    = $wp_obj->ID;
        $page_title = $wp_obj->post_title;

        // 親ページがあれば順番に表示
        if ( $wp_obj->post_parent !== 0 ) {
            $parent_array = array_reverse( get_post_ancestors( $page_id ) );
            foreach( $parent_array as $parent_id ) {
                $page = get_post($parent_id);
                if($page->post_status == "publish"){
                    echo '<li>'.
                        '<a href="'. get_permalink( $parent_id ).'">'.
                        '<span>'.get_the_title( $parent_id ).'</span>'.
                        '</a>'.
                        '</li>';
                }else{
                    echo '<li>'.
                        '<span>'.get_the_title( $parent_id ).'</span>'.
                        '</li>';
                }

            }
        }
        // 投稿自身の表示
        echo '<li><span>'. $page_title .'</span></li>';

    } elseif ( is_post_type_archive() ) {

        /**
         * 投稿タイプアーカイブページ ( $wp_obj : WP_Post_Type )
         */
        echo '<li><span>'. $wp_obj->label .'</span></li>';

    } elseif ( is_date() ) {

        /**
         * 日付アーカイブ ( $wp_obj : null )
         */
        $year  = get_query_var('year');
        $month = get_query_var('monthnum');
        $day   = get_query_var('day');

        if ( $day !== 0 ) {
            //日別アーカイブ
            echo '<li><a href="'. get_year_link( $year ).'"><span>'. $year .'年</span></a></li>'.
                '<li><a href="'. get_month_link( $year, $month ). '"><span>'. $month .'月</span></a></li>'.
                '<li><span>'. $day .'日</span></li>';

        } elseif ( $month !== 0 ) {
            //月別アーカイブ
            echo '<li><a href="'. get_year_link( $year ).'"><span>'.$year.'年</span></a></li>'.
                '<li><span>'.$month . '月</span></li>';

        } else {
            //年別アーカイブ
            echo '<li><span>'.$year.'年</span></li>';

        }

    } elseif ( is_author() ) {

        /**
         * 投稿者アーカイブ ( $wp_obj : WP_User )
         */
        echo '<li><span>'. $wp_obj->display_name .' の執筆記事</span></li>';

    } elseif ( is_archive() ) {

        /**
         * タームアーカイブ ( $wp_obj : WP_Term )
         */
        $term_id   = $wp_obj->term_id;
        $term_name = $wp_obj->name;
        $tax_name  = $wp_obj->taxonomy;

        /* ここでタクソノミーに紐づくカスタム投稿タイプを出力しても良いでしょう。 */

        // 親ページがあれば順番に表示
        if ( $wp_obj->parent !== 0 ) {

            $parent_array = array_reverse( get_ancestors( $term_id, $tax_name ) );
            foreach( $parent_array as $parent_id ) {
                $parent_term = get_term( $parent_id, $tax_name );
                echo '<li>'.
                    '<a href="'. get_term_link( $parent_id, $tax_name ) .'">'.
                    '<span>'. $parent_term->name .'</span>'.
                    '</a>'.
                    '</li>';
            }
        }

        // ターム自身の表示
        echo '<li>'.
            '<span>'. $term_name .'</span>'.
            '</li>';


    } elseif ( is_search() ) {

        /**
         * 検索結果ページ
         */
        echo '<li><span>「'. get_search_query() .'」で検索した結果</span></li>';


    } elseif ( is_404() ) {

        /**
         * 404ページ
         */
        echo '<li><span>お探しの記事は見つかりませんでした。</span></li>';

    } else {

        /**
         * その他のページ（無いと思うが一応）
         */
        echo '<li><span>'. get_the_title() .'</span></li>';
    }

    echo '</ul></div>';  // 冒頭に合わせて閉じタグ

}


//ページネーション
function pagenation(){

}

//非公開・下書きでも固定ページを親に設定できる
add_filter('page_attributes_dropdown_pages_args','add_private_draft');
function add_private_draft($args) {
    $args['post_status'] = 'publish,private,draft';
    return $args;
}

add_filter( 'quick_edit_dropdown_pages_args', function( $dropdown_args ) {
    $dropdown_args['post_status'] = array( 'publish', 'future', 'draft', 'pending', 'private' );
    return $dropdown_args;
} );


//非公開のタイトルから「非公開：」を削除
function remove_page_title_prefix($title='' ){
    if ( empty( $title ) || !is_page() ) return $title;
    $search[0] = '/^' . str_replace('%s', '(.*)', preg_quote(__('Protected: %s'), '/' )) . '$/';
    $search[1] = '/^' . str_replace('%s', '(.*)', preg_quote(__('Private: %s'), '/' )) . '$/';
    return preg_replace($search, '$1', $title);}
add_filter( 'the_title', 'remove_page_title_prefix' );



//再帰処理サンプル
class Sample {
    public function __construct () {
        $this->list_page_render();
    }

    public function list_page_render () {
        echo 'Current PHP version: ' . phpversion() . PHP_EOL;

        // カテゴリー階層配列(idはユニーク)
        $terms = array(
            array( 'id' => 1, 'parent_id' => 0, 'name' => 'Size' ),
            array( 'id' => 2, 'parent_id' => 0, 'name' => 'Gender' ),
            array( 'id' => 3, 'parent_id' => 1, 'name' => 'L' ),
            array( 'id' => 4, 'parent_id' => 1, 'name' => 'M' ),
            array( 'id' => 5, 'parent_id' => 2, 'name' => 'Men' ),
            array( 'id' => 6, 'parent_id' => 5, 'name' => 'Pants' ),
            array( 'id' => 7, 'parent_id' => 2, 'name' => 'Women' ),
            array( 'id' => 8, 'parent_id' => 7, 'name' => 'Skirt' )
        );

        // 親IDだけの配列を作成
        $parent_ids = array();
        foreach ( $terms as $term ) {
            $parent_ids[] = $term['parent_id'];
        }

        // カテゴリーの最下層のidを配列に保持
        $term_bottom = array();
        foreach ( $terms as $term ) {
            if ( !in_array( $term['id'], $parent_ids ) ) {
                $term_bottom[] = $term['id'];
            }
        }

        // 最下層の配列をループして木構造の頂点まで（$parent_id = 0）
        $categories = array();
        foreach ( $term_bottom as $term_id ) {
            $categories[] = $this->set_ids( $term_id, $terms );
        }
        var_dump( $categories );
    }

    private function set_ids ( $id, $terms, $args = array() ) {
        if ( $id == 0 ) {
            return array_reverse( $args );
        } else {
            $args[] = $id;

            foreach ( $terms as $term ) {
                if ( $term['id'] == $id ) {
                    return $this->set_ids( $term['parent_id'], $terms, $args );
                }
            }
        }
    }
}