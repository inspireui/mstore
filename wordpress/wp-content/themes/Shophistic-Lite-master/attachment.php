<?php get_header(); ?>

<?php get_template_part( "/templates/beforeloop", "attachment" ) ?> 
            
    <?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
    
            <?php get_template_part( "/templates/content", "attachment" ) ?>

            <div class="clearfix"></div>
    
            <?php comments_template(); ?>
    
        <?php endwhile; else: ?>
    
            <?php get_template_part( "/templates/content", "none" ); ?>
    
    <?php endif; ?>

<?php get_template_part( "/templates/afterloop", "attachment" ) ?> 

<?php get_footer(); ?>