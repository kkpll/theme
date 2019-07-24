<?php get_header(); ?>

<h1><?php echo get_page_title(); ?></h1>

<?php if(have_posts()){ ?>

        <?php

        $post_type = get_post_type();

        while(have_posts()){
            the_post();
            $data = get_post_data();
            get_template_part('template/single',$post_type);
        }

        ?>

<?php }else{ echo "※記事がありません"; } ?>

<?php get_footer(); ?>
