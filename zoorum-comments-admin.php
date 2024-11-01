<?php
/**
 * Displays admin page for Zoorum Comments plugin.
 *
 *
 * @package zoorum-comments
 * @since zoorum-comments 0.4
 */

function zoorum_admin_options() {
	?><div class="wrap"><h2><?php _e('Zoorum Comments options', 'zoorum-comments'); ?></h2><?php 
	if($_REQUEST['submit']) {
		zoorum_set_options();
	}
	zoorum_display_options_form();
	?></div><?php
}
function zoorum_set_options() {
	if ($_REQUEST['zoorum_url']) {
		update_option('zoorum_url', $_REQUEST['zoorum_url']);
		update_option('zoorum_api_key', $_REQUEST['zoorum_api_key']);
		update_option('zoorum_show_error', $_REQUEST['zoorum_show_error'] ? 'true' : 'false');
		$ok = true;
	}
	if ($ok) {
		?>
				<div id="message">
					<p><?php _e('Options saved.', 'zoorum-comments'); ?></p>
				</div><?php 
			} else {
				?>
				<div id="message">
					<p><?php _e('Options failed to save.', 'zoorum-comments'); ?></p>
				</div><?php
			}
}

function zoorum_display_options_form() {
	$old_zoorum_url = get_option('zoorum_url');
	$old_zoorum_api_key = get_option('zoorum_api_key');
	$old_zoorum_show_error = get_option('zoorum_show_error');
	?><form method="post">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="zoorum_url"><?php _e('Zoorum URL', 'zoorum-comments'); ?></label>
						</th>
						<td>
							<input type="text" name="zoorum_url" value="<?php echo($old_zoorum_url); ?>" class="regular-text" />
							<p class="description"><?php _e('The url of the zoorum forum. ex. http://test.zoorum.com/. Including the trailing slash!', 'zoorum-comments'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="zoorum_api_key"><?php _e('Zoorum API key', 'zoorum-comments'); ?></label>
						</th>
						<td>
							<input type="text" name="zoorum_api_key" value="<?php echo($old_zoorum_api_key); ?>" class="regular-text" />
							<p class="description"><?php _e('The API Key from zoorum.', 'zoorum-comments'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="zoorum_api_key"><?php _e('Display error messages', 'zoorum-comments'); ?></label>
						</th>
						<td>
							<label for="zoorum_show_error">
								<input type="checkbox" id="zoorum_show_error" value="true" name="zoorum_show_error" <?php if (filter_var($old_zoorum_show_error, FILTER_VALIDATE_BOOLEAN)) echo ('checked="checked"');  ?> />
								 <?php _e('Display error messages whenever communication to zoorum is corrupted.', 'zoorum-comments'); ?>
							</label>
							<!-- <p class="description"><?php _e('Display error messages whenever communication to zoorum is corrupted.', 'zoorum-comments'); ?></p> -->
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" value="<?php _e('Save changes', 'zoorum-comments'); ?>" class="button button-primary" /></p>
		</form><?php
}
?>