<?php if( !is_page() ){ ?>
<div class="metadata">
	<ul>
		<li class="meta_date"><i class="fa fa-clock-o"></i> <?php the_time('F j, Y'); ?></li>
        <li class="meta_author"><i class="fa fa-user"></i> <?php the_author() ?></li>
        <li class="meta_category"><i class="fa fa-folder-open"></i> <?php the_category(', ') ?></li>
        <?php if(get_the_tags()){ ?>
            <li class="meta_tags"><i class="fa fa-tag"></i> <?php the_tags('', '', ''); ?></li>
        <?php } ?>
        <li class="meta_comments"><i class="fa fa-comment"></i> <?php comments_popup_link(__('No Comments', 'shophistic-lite'), __('1 Comment', 'shophistic-lite'), __('% Comments', 'shophistic-lite')); ?></li>
    </ul>
    <div class="clearfix"></div>
</div><!-- /metadata -->
<?php } ?>
			            	
			            		