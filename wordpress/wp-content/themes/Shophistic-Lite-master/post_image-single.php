<?php
if ( has_post_thumbnail() ) {
?>
<div class="post_image">
	<?php the_post_thumbnail('post'); ?>
</div><!-- /post_image -->
<?php
}
?>