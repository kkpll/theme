<?php
$related_posts = get_the_related_posts();
if($related_posts['posts']){
    ?>
    <ul class="related_posts">
        <h4 class="related_posts__title">「<?php echo $related_posts['title']; ?>」の関連記事はこちら</h4>
        <?php
        foreach($related_posts['posts'] as $p){
            ?>
            <li class="related_posts__list"><a href="<?php echo $p['permalink']; ?>"><?php echo $p['title']; ?></a></li>
        <?php } ?>
    </ul>
<?php } ?>
