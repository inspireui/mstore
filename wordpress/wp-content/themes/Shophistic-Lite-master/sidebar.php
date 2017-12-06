<?php 

if ( is_active_sidebar( 'Sidebar Widgets' ) ) { 

?>
    <aside id="sidebar" class="col-md-2 col-md-pull-10">

		<?php
		if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'Sidebar Widgets' ) ) : else :
        endif;
        ?>

        <div class="clearfix"></div>
	</aside>

<?php }//if is_active_sidebar ?>  