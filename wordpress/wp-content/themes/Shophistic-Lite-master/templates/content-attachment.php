<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        
    <?php 
    if ( is_single() ) :
        the_title( '<h1 class="post_title">', '</h1>' );
    else :
        the_title( sprintf( '<h2 class="post_title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
    endif;
    ?>

    <?php get_template_part( "post_image", "attachment" ); ?> 

    <div class="entry">
        <?php echo preg_replace('/&lt;img[^&gt;]+./','',get_the_content()); ?>
        <div class="clearfix"></div>
    </div>

        <div class="clearfix"></div>
</article>