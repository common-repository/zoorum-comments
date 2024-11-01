<?php
/**
 * Zoorum widget which displays the latest posts on the forum
 *
 *
 * @package zoorum-comments
 * @since zoorum-comments 0.7
 */
class Zoorum_Feed_Widget extends WP_Widget {

	public function __construct() {
		// widget actual processes
		parent::__construct(
	 		'zoorum_feed_widget', // Base ID
			__('Zoorum Forum Feed', 'zoorum-comments'), // Name
			array( 'description' => __( 'A widget to display the recent feed from forum', 'zoorum-comments' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget
		extract( $args );
		echo $before_widget;
		$title = apply_filters( 'widget_title', $instance['title'] );
		if(!$title || $title != '') {
			$title = __('Latest forum activity', 'zoorum-comments');
		}
		echo $before_title.$title.$after_title;
		?>
		<div class="zoorum-latest-feed"></div>
		<div class="zoorum-ajax-url"><?php echo(admin_url('admin-ajax.php')); ?></div>
		<div class="zoorum-widget-forum-url-wrapper">
			<a class="zoorum-widget-forum-url more-link" target="_blank" href="<?php echo(get_option('zoorum_url')); ?>"><?php _e('Go to the forum', 'zoorum-comments'); ?> &raquo;</a>
		</div>
		<?php
		echo $after_widget;
	}

 	public function form( $instance ) {
		// outputs the options form on admin
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = '';
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}
?>