<?php
/**
 * @package zoorum-comments
 * @version 0.9
 */
/*
Plugin Name: Zoorum Comments
Plugin URI: http://www.zoorum.com/
Description: Zoorum Comments connects to your Zoorum forum. Each Wordpress article that is commented is also reflected as a new topic on your Zoorum forum.
Author: Zoorum
Author URI: http://www.zoorum.com/
Version: 0.9
License: GPL2
 */

/*  Copyright 2013  Zoorum

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/* Some notes:
 * * If Title is less than 5 characters 401(wrong api-key) will be returned when creating new thread.
 * * The webhost needs either curl or allow_url_fopen
 * * The meta box widget will contain a useless comment rss feed
 * * The nr of comments will be updated when a user views the post in question
 * * Any "nr of comments" displayed before the comment form in a single post will not be updated until page is refreshed
 *   - Solution: Read the zoorum thread in an earlier stage hook, 
 *               put it in post meta, and load it from post meta instead.
 * * */


/* Options */
define('ZOORUM_PLUGIN_URL', plugin_dir_url( __FILE__ ));



/* Activate and deactivate */
function set_zoorum_options() {
	add_option('zoorum_url', 'http://test.zoorum.com/', 'The url of the zoorum forum. ex. http://test.zoorum.com/', 'zoorum-comments');
	add_option('zoorum_api_key', '', 'The API Key from zoorum.', 'zoorum-comments');
	add_option('zoorum_show_error', 'false', 'Display error messages.', 'zoorum-comments');
}
register_activation_hook(__FILE__, 'set_zoorum_options');



/* Admin menu */
include_once dirname( __FILE__ ) .'/zoorum-comments-admin.php';
function modify_admin_menu() {
	add_options_page(
			'Zoorum Comments',
			'Zoorum Comments',
			'manage_options',
			__FILE__,
			'zoorum_admin_options'
	);
}add_action('admin_menu', 'modify_admin_menu');



/* Deprecated - Or is it? */
function zoorum_add_help_text_to_title() {
    echo '<div class="zoorum-title-test">'.__('Note! Title must contain 5 characters to enable creation of a zoorum-thread on reply', 'zoorum-comments').'</div>';
}
// add_action('edit_form_after_title', 'zoorum_add_help_text_to_title');



/* i18 */
function zoorum_lang_textdomain() {
    load_plugin_textdomain( 'zoorum-comments', false, dirname( plugin_basename( __FILE__ ) ).'/lang' );
}
add_action('init', 'zoorum_lang_textdomain');



/* Widgets */
include_once dirname( __FILE__ ) .'/inc/zoorum-comments-widget.php';
function zoorum_register_widgets() {
    register_widget( 'Zoorum_Feed_Widget');
}
add_action('widgets_init', 'zoorum_register_widgets');



/* Comment Template */
function zoorum_comments_template() {
	return dirname(__FILE__) . '/zoorum-comments-tpl.php';
}
add_filter("comments_template", 'zoorum_comments_template', 512);



/* Style and Javascript */
function zoorum_css_js() {
    wp_register_style( 'zoorum-comments.css', ZOORUM_PLUGIN_URL . 'css/zoorum-comments.css', array());
    wp_enqueue_style( 'zoorum-comments.css');
    wp_register_script( 'zoorum-comments.js', ZOORUM_PLUGIN_URL . 'js/zoorum-comments.js', array('jquery'));
    wp_enqueue_script( 'zoorum-comments.js' );
}
add_action('init', 'zoorum_css_js');



/* Utility functions */
include_once dirname( __FILE__ ) .'/inc/zoorum-comments-utils.php';
add_filter('get_comments_number', 'zoorum_get_post_count');



/* Ajax callbacks */
include_once dirname( __FILE__ ) .'/inc/zoorum-comments-ajax-callbacks.php';
add_action('wp_ajax_zoorum_create_topic', 'zoorum_create_topic_callback');
add_action('wp_ajax_nopriv_zoorum_create_topic', 'zoorum_create_topic_callback');

add_action('wp_ajax_zoorum_get_topic', 'zoorum_get_topic_callback');
add_action('wp_ajax_nopriv_zoorum_get_topic', 'zoorum_get_topic_callback');

add_action('wp_ajax_zoorum_get_feed', 'zoorum_get_feed_callback');
add_action('wp_ajax_nopriv_zoorum_get_feed', 'zoorum_get_feed_callback');


?>