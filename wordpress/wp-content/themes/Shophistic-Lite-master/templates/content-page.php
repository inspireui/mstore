<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
        
    <?php 
    if ( ! is_front_page() ) { 
        if ( is_singular() ) :
            the_title( '<h1 class="page-title">', '</h1>' );
        else :
            the_title( sprintf( '<h2 class="page-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
        endif;
    }
    ?>
    
    

    <div class="entry">
        <?php the_content(); //Read more button is in framework/functions/single_functions.php?>
        <div class="clearfix"></div>
    </div>

    <?php
    wp_link_pages( array(
        'before'      => '<div class="page-links">',
        'after'       => '</div>',
        'link_before' => '<span>',
        'link_after'  => '</span>',
        'pagelink'    => esc_attr__( 'Page', 'shophistic-lite' ) . ' %',
        'separator'   => '',
    ) );
    ?>


    <div class="clearfix"></div>
</article>

<?php 
// If comments are open or we have at least one comment, load up the comment template.
if ( comments_open() || get_comments_number() ) :
    comments_template();
endif;
?>