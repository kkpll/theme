<?php global $data; ?>
<li class="post">
            <a href="<?php echo $data['permalink']; ?>"><img src="<?php echo $data['thumbnail']; ?>"></a>
            <a href="<?php echo $data['permalink']; ?>"><h2><?php echo $data['title']; ?></h2></a>
<div class="categories">
    <?php foreach((array)$data['category'] as $category){ ?>
        <a href="<?php echo $category['link']; ?>"><?php echo $category['name']; ?></a>
    <?php } ?>
</div>
<div class="tags">
    <?php foreach((array)$data['post_tag'] as $tag){ ?>
        <a href="<?php echo $tag['link']; ?>"><?php echo $tag['name']; ?></a>
    <?php } ?>
</div>
<div class="taxonomies">
    <?php foreach((array)$data['voicecat'] as $tax){ ?>
        <a href="<?php echo $tax['link']; ?>"><?php echo $tax['name']; ?></a>
    <?php } ?>
</div>
</li>