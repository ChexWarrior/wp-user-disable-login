<?php declare(strict_types=1);

/**
 * Plugin Name:     User Disable
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     user-disable
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         User_Disable
 */

namespace User_Disable;

use WP_Error;
use WP_User;

function usermeta_form_field_disabled(WP_User $user)
{
	?>
	<h3>Disable User Login</h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="disabled">Is Disabled</label>
			</th>
			<td>
				<input type="checkbox"
					id="disabled"
					name="disabled"
					<?php
						echo esc_attr(get_user_meta($user->ID, 'disabled', true))
							? 'checked' : ''
					?>
					title="If checked user will not be able to login"
					required>
				<p class="description">
					If checked user will not be able to login.
				</p>
			</td>
		</tr>
	</table>
	<?php
}

function usermeta_form_field_disabled_update(int $user_id)
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	$disabled = $_POST['disabled'] === 'on';

	return update_user_meta(
		$user_id,
		'disabled',
		$disabled
	);
}

add_action('show_user_profile', 'User_Disable\usermeta_form_field_disabled');
add_action('edit_user_profile', 'User_Disable\usermeta_form_field_disabled');
add_action('personal_options_update', 'User_Disable\usermeta_form_field_disabled_update');
add_action('edit_user_profile_update', 'User_Disable\usermeta_form_field_disabled_update');

function check_if_user_disabled(WP_User|WP_Error $user, string $password)
{
	$disabled = get_user_meta($user->ID, 'disabled', true) === "1";

	if ($disabled) {
		return new WP_Error('user_disabled', 'User is disabled', $user->ID);
	}

	return $user;
}

add_filter('wp_authenticate_user', 'User_Disable\check_if_user_disabled', 10, 2);
