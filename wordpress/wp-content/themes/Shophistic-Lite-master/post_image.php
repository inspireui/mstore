<?php
if ( has_post_thumbnail() ) {
?>
<div class="post-image">
	<?php 
	if ( ! is_single() ) {
	?>
	<a href="<?php echo esc_url( get_permalink() ) ?>" class="ql_thumbnail_hover" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
		<span><i class="fa fa-chevron-right"></i></span>
	<?php } ?>

		<?php the_post_thumbnail('post'); ?>
	
	<?php if ( ! is_single() ) { ?>
	</a>
	<?php } ?>
</div><!-- /post_image -->
<?php
}
?>