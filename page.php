<?php get_header(); ?>

<h1><?php echo get_page_title(); ?></h1>

<?php if(have_posts()){ ?>

    <?php

    $post_type = get_post_type();

    while(have_posts()){
        the_post();
    ?>

        <h2><?php echo $data['title']; ?></h2>
        <div class="content">
            <?php the_content(); ?>
        </div>

   <?php } ?>

<?php }else{ echo "※記事がありません"; } ?>

<?php get_footer(); ?>
