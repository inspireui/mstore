<?php
/*
Template Name: Full Width
*/
?>
<?php get_header(); ?>

<?php get_template_part( "/templates/beforeloop", "fullwidth" ) ?> 

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <?php get_template_part( "/templates/content", "page" ) ?>

    <?php endwhile; else: ?>

        <?php get_template_part( "/templates/content", "none" ); ?>

    <?php endif; ?>

<?php get_template_part( "/templates/afterloop", "fullwidth" ) ?> 

<?php get_footer(); ?>