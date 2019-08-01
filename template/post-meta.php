<?php global $data; ?>
<div class="post">
    <div class="post__meta">
        <span class="post__meta__date"><?php echo $data['date']; ?></span>
        <span class="post__meta__cats">
                            <?php
                            foreach($data['category'] as $cats){
                                foreach($cats as $cat){
                                    if($cat['name']){
                                        ?>
                                        <span class="post__meta__cats__cat post__meta__cats__cat-<?php echo $data['post_type']; ?>">[<?php echo $cat['name']; ?>]</span>
                                        <?php
                                    }
                                }
                            }
                            ?>
                            </span>
        <span class="post__meta__tags">
                            <?php
                            foreach($data['tag'] as $tags){
                                foreach($tags as $tag){
                                    if($tag['name']){
                                        ?>
                                        <span class="post__meta__cats__cat post__meta__cats__cat-<?php echo $data['post_type']; ?>">#<?php echo $tag['name']; ?></span>
                                        <?php
                                    }
                                }
                            }
                            ?>
        </span>
    </div>
</div>