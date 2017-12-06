<div id="container" class="container">
    <section id="main" role="main" class="row">
        <div class="content_background">
        	<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'shophistic-lite' ), get_search_query() ); ?></h1>

            <div id="content" class="<?php echo esc_attr( shophistic_lite_content_check_sidebar() ); ?>">