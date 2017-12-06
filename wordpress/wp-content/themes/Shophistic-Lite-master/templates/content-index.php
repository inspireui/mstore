<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >

    <?php get_template_part( "post_image", "content" ); ?>

    <div class="post_content row">
        <div class="col-md-6">
                <?php 
                if ( is_singular() ) :
                    the_title( '<h1 class="post_title">', '</h1>' );
                else :
                    the_title( sprintf( '<h2 class="post_title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
                endif;
                ?>

            <?php get_template_part( "meta", "content" ); ?>

        </div><!-- /col-md-6 -->

        <div class="col-md-6">
            <div class="entry">
                <?php 
                if ( is_archive() || is_search() ) {
                    the_excerpt();
                }else{
                    the_content(); //Read more button is in framework/functions/single_functions.php
                }
                ?>
                <div class="clearfix"></div>
            </div>
            
        </div><!-- /col-md-6 -->
    </div><!-- /post_content -->

    <div class="clearfix"></div>
</article>