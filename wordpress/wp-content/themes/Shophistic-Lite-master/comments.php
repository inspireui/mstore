<div id="comments">
	<?php
    
        if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
            die ('Please do not load this page directly. Thanks!');
    
        if ( post_password_required() ) { 
            _e("This post is password protected. Enter the password to view comments.", 'shophistic-lite');
       
            return;
        }
    ?>
    
    <?php if ( have_comments() ) : ?>
        
        <h3 class=""><?php comments_number(__('No Responses', 'shophistic-lite'), __('One Response', 'shophistic-lite'), __('% Responses', 'shophistic-lite') );?></h3>
    
        <div class="navigation">
            <div class="next-posts"><?php previous_comments_link() ?></div>
            <div class="prev-posts"><?php next_comments_link() ?></div>
        </div>
    
        <ol class="commentlist">
            <?php 
            $args = array(
				'callback'          => 'shophistic_lite_comment',
				'type'              => 'comment'
			);
            wp_list_comments($args);
            ?>
        </ol><!-- /commentlist-->
    
        <div class="navigation">
            <div class="next-posts"><?php previous_comments_link() ?></div>
            <div class="prev-posts"><?php next_comments_link() ?></div>
        </div>
        
     <?php else : // this is displayed if there are no comments so far ?>
    
        <?php if ( comments_open() ) : ?>
            <!-- If comments are open, but there are no comments. -->
    
         <?php else : // comments are closed ?>
            <p><?php //_e("Comments are closed.",'shophistic-lite');?></p>
    
        <?php endif; ?>
        
    <?php endif; ?>
    
    


</div><!-- /comments-->




<?php if ( comments_open() ) : ?>




	<?php 
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	$comments_args = array(
	        // remove "Text or HTML to be displayed after the set of comment fields"
	        'comment_notes_after' => '',
	        
	        // redefine your own textarea (the comment body)
	        'comment_field' => '<div class="input-wrap textarea clearfix">
							      <label class="control-label" for="comment">'. __("Comment", 'shophistic-lite') .'</label>
							      <div class="controls-wrap">
									    <textarea class="input-xlarge" name="comment" id="comment" tabindex="4" rows="3"></textarea>
							      </div>
								</div>',

			'id_submit' => 'submit-respond',

			'fields' => apply_filters( 'comment_form_default_fields', array(


						'author' =>	'<div class="input-wrap">
								      <label class="control-label" for="author">'. __("Name",'shophistic-lite').''. ( $req ? ' (*)' : '' ).'</label>
								      <div class="controls-wrap">
									      	<i class="fa fa-user"></i>
										    <input class="input-xlarge" type="text" name="author" id="author" value="'.  esc_attr($comment_author) .'" size="22" tabindex="1" ' . $aria_req . ' />
											
								      </div>
								    </div>',
						
						'email' =>	'<div class="input-wrap">
								      <label class="control-label" for="email">'. __("Email",'shophistic-lite') .''. ( $req ? ' (*)' : '' ).'</label>
								      <div class="controls-wrap">
									      	<i class="fa fa-envelope"></i>
										    <input class="input-xlarge" type="text" name="email" id="email" value="'.  esc_attr($commenter['comment_author_email']).'" size="22" tabindex="2" ' . $aria_req . ' />
								      </div>
								    </div>',


						'url' =>	'<div class="input-wrap">
								      <label class="control-label" for="url">'. __("Website",'shophistic-lite').'</label>
								      <div class="controls-wrap">
									      	<i class="fa fa-link"></i>
										    <input class="input-xlarge" type="text" name="url" id="url" value="'.  esc_attr($commenter['comment_author_url']).'" size="22" tabindex="3" />
								      </div>
								    </div>'
						)
			)

	);

	comment_form($comments_args); 

	?> 




<div class="clearfix"></div> 

<?php endif; ?>