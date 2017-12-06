<?php get_header(); ?>

	<?php get_template_part( "/templates/beforeloop", "fullwidth" ) ?> 
                    
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
                    
            <h2><?php esc_html_e( 'Error 404 - Not Found', 'shophistic-lite' ); ?></h2>
            <p><?php esc_html_e( 'Sorry, but the requested resource was not found on this site. Please try again or contact the administrator for assistance.', 'shophistic-lite' ); ?></p>
                    
            <div class="clearfix"></div>
                        
        </article>
    <?php get_template_part( "/templates/afterloop", "fullwidth" ) ?> 

<?php get_footer(); ?>