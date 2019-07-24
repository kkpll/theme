<?php
get_header();
?>

<?php
//ページタイトル
$page_title = get_page_title();


if(have_posts()){
    while(have_posts()){
        the_post();

        //記事ループ

    }

    //ページネーション（プラグイン：WP-PageNavi）
    if(function_exists('wp_pagenavi')){ wp_pagenavi(); }

}else{
    echo "※記事がありません";
}
?>

<?php
get_footer();
?>
