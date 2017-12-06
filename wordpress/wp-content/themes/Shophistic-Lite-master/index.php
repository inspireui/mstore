<?php get_header(); ?>

<?php get_template_part( "/templates/beforeloop", "index" ); ?>   

	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<?php get_template_part( "/templates/content", "index" ) ?>

		<?php endwhile; ?>

		<?php get_template_part( "pagination", "index" ); ?>
		
	<?php else : ?>

		<?php get_template_part( "/templates/content", "none" ); ?>

	<?php endif; ?>

<?php get_template_part( "/templates/afterloop", "index" ) ?> 


<?php get_footer(); ?>