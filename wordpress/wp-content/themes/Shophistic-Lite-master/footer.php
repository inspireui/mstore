        <div class="footer_wrap ">


                <footer id="footer" class="container">
                    <div class="sub_footer">
                            
                        <p>
                        <?php esc_html_e( 'Powered by ', 'shophistic-lite' ); ?><a href="<?php echo esc_url( __( 'https://wordpress.org/', 'shophistic-lite' ) ); ?>"><?php esc_html_e( 'WordPress', 'shophistic-lite' ); ?></a>. <?php printf( esc_html__( 'Theme: %1$s by %2$s.', 'shophistic-lite' ), 'Shophistic Lite', '<a href="https://www.quemalabs.com/" rel="designer">Quema Labs</a>' ); ?>
                        </p>

                        <?php get_template_part( '/templates/menu', 'social' ); ?>
                           
                        <div class="clearfix"></div>
                    </div><!-- /sub_footer -->
                </footer><!-- /footer -->


            <div class="clearfix"></div>
                
        </div><!-- /footer_wrap -->

        </div><!-- /wrap -->

    
        
    <!-- WP_Footer --> 
    <?php wp_footer(); ?>
    <!-- /WP_Footer --> 

      
    </body>
</html>