<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package zoorum-comments
 * @since zoorum-comments 0.4
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() )
	return;
?>


<div id="comments" class="zoorum-comments-wrapper comments-area comment-respond">
    <div id="respond"></div>
    <script id="zoorum-comment-template" type="text/template">
        <?php zoorum_single_post($z_post); ?>
    </script>
    <script id="zoorum-form-comment-template" type="text/template">
        <?php zoorum_comment_form(0,TRUE,FALSE); ?>
    </script>
    <script id="zoorum-form-create-template" type="text/template">
        <?php zoorum_comment_form(0, FALSE, TRUE); ?>
    </script>

    <?php
    $z_postcount = zoorum_get_post_count();
	?>
	<h3 id="comments-title" class="zoorum-comments-title comments-title">
		<?php
		if($z_postcount > 0) {
		    printf( _n( 'One comment on &ldquo;%2$s&rdquo;', '%1$s Comments on &ldquo;%2$s&rdquo;', $z_postcount, 'zoorum-comments' ),
		        number_format_i18n( $z_postcount ), '<span>' . get_the_title() . '</span>' );
		} else {
		    if( comments_open( get_the_ID() ) )
		        printf(__('Be the first to comment &ldquo;%1$s&rdquo;', 'zoorum-comments'), '<span>' . get_the_title() . '</span>');
		}
		?>
	</h2>
	
	<div id="commentform" class="comments-list comment-list zoorum-thread <?php $z_islocked ? 'islocked' : ''  ?>">
	    <div id="zoorum-comment-create-values">
            <div id="zoorum-wp-pid"><?php echo(get_the_ID()); ?></div>
            <div id="zoorum-ajax-url"><?php echo(admin_url('admin-ajax.php')); ?></div>
            <div id="zoorum-url"><?php echo(get_option('zoorum_url')); ?></div>
        </div>
        <?php
        echo '<div id="zoorum-comments-error" style="display: '.
            (filter_var(get_option('zoorum_show_error'),FILTER_VALIDATE_BOOLEAN)? 'block' : 'none').';">';
        echo '</div>';
    	if( comments_open(get_the_ID()) ) {
    	    if( !$z_islocked ) {
    	        if( $z_threadid ) {
    	            zoorum_comment_form($z_array->ThreadID);
    	        } else {
    	            zoorum_comment_form(0, FALSE, TRUE);
    	        }
    	    } else {
    	        _e('Comments are locked', 'zoorum-comments');
    	    }
    	}
    	?>
    	<div class="zoorum-footer">
    	    <?php if( comments_open(get_the_ID()) ) { 
                if ($z_threadid ) { ?><a target="_blank" class="zoorum-link" href="<?php echo(get_option('zoorum_url').$z_threadid); ?>" href=""><?php _e('Continue this discussion on the forum', 'zoorum-comments'); ?></a><?php }
                else { ?><a target="_blank" class="zoorum-link" href="<?php echo(get_option('zoorum_url')); ?>" href=""><?php _e('Continue this discussion on the forum', 'zoorum-comments'); ?></a><?php } ?>
    	    <?php }
             ?>
    	</div>
    </div>
    
</div>
<?php 



/**
 * 
 * @param unknown_type $z_post
 */
function zoorum_get_post_classes($z_post) {
    $z_classstr = 'zoorum-post';
    if ($z_post->IsFirst)
        $z_classstr .= ' zoorum-first';
    else
        $z_classstr .= ' zoorum-reply';
    if ($z_post->IsComment)
        $z_classstr .= ' zoorum-comment';
    return $z_classstr;
}

function zoorum_single_post($z_post, $z_id_to_comment) {
    ?>
    
    <div id="post<?php echo $z_post->PostID ?>" class="<?php echo zoorum_get_post_classes($z_post)?>">
        <div class="zoorum-text">
            <div class="zoorum-posttext">
	            <?php echo $z_post->User->Username;?>:
	            <?php echo $z_post->ParsedText?>
	        </div>
	        <div class="zoorum-meta">
	            <span class="zoorum-datestamp" title="<?php echo $z_post->ReadableDate; ?>">
	                <?php echo $z_post->TimeDiffDate; ?>
	            </span>
	        </div>
	        <div class="zoorum-actions">
	            <a class="zoorum-comment-link" href="#"><span class="id-to-comment" style="display: none;"><?php echo $z_id_to_comment;?></span><?php _e('Comment', 'zoorum-comments'); ?></a>
	        </div>
	    </div>
	</div>
	<?php
}
function zoorum_comment_form($z_id_to_comment = 0, $z_comment_bool = FALSE, $z_create = FALSE) {
    ?>
    <div class="form-submit zoorum-comment-form-wrapper zoorum-reply <?php echo( $z_comment_bool ? 'zoorum-comment' : ''); ?>" id="zoorum-comment-form-wrapper-<?php echo $z_id_to_comment;?>">
        <div class="zoorum-text">
            <div class="zoorum-posttext">
                <form
                action="<?php echo get_option('zoorum_url'). 'post/' . ($z_comment_bool ? 'commentWithAutologin' : 'replyWithAutologin')?>"
                method="post"
                target="_blank"
                class="zoorum-comment-form <?php echo($z_create ? 'zoorum-submit-create' : 'zoorum-submit-comment');?>" >
                    <textarea type="text" name="zoorum-message" id="zoorum-message" rows=1 ></textarea>
                    <input type="hidden" name="message" id="post-message" />
                    <input type="hidden" id="zoorum-id" name="<?php echo ($z_comment_bool ? 'post' : 'topic') ?>id" value="<?php echo $z_id_to_comment ?>" />
                    <input type="submit" name="zoorum-submit" id="submit" value="<?php _e('Send', 'zoorum-comments'); ?>" />
                    <div class="zoorum-clear-div"></div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
 ?>