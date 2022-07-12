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
use WP_Session_Tokens;
use WP_User;

// Add disabled field to user edit form
function usermeta_form_field_disabled(WP_User $user)
{
	if (current_user_can('disable_users', $user->ID)): ?>
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
	<?php endif;
}

// Ensure meta data is updated and disabled users are logged out
function usermeta_form_field_disabled_update(int $user_id): int|bool
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	$disabled = $_POST['disabled'] === 'on';

	// Logout target user when they are disabled
	if ($disabled) {
		$sessions = WP_Session_Tokens::get_instance($user_id);
		$sessions->destroy_all();
	}

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

// Ensure we check disabled meta when a user logs in
function check_if_user_disabled(WP_User|WP_Error $user, string $password): WP_User|WP_Error
{
	$disabled = get_user_meta($user->ID, 'disabled', true) === "1";

	if ($disabled) {
		return new WP_Error('user_disabled', 'User is disabled', $user->ID);
	}

	return $user;
}

add_filter('wp_authenticate_user', 'User_Disable\check_if_user_disabled', 10, 2);

// Show User Disabled Column
function add_user_disabled_column(array $columns): array
{
	$check_column = $columns['cb'];
	unset($columns['cb']);
	return array_merge([
		'cb' => $check_column,
		'user_disabled' => 'User Disabled'
	],
	$columns);
}

function show_user_disabled_column($value, $column_name, $user_id)
{
	if ($column_name === 'user_disabled') {
		$user_data = get_userdata($user_id);
		return $user_data->get('disabled') === "1"
			? '<strong class="file-error">Disabled</strong>' : '';
	}

	return $value;
}

add_filter('manage_users_columns', 'User_Disable\add_user_disabled_column');

add_filter('manage_users_custom_column', 'User_Disable\show_user_disabled_column', 10, 3);

// Add User Disable/Enable Bulk Actions
function register_enable_disable_bulk_actions($bulk_actions)
{
	$bulk_actions['disable_user'] = __('Disable User', 'disable_user');
	$bulk_actions['enable_user'] = __('Enable User', 'enable_user');

	return $bulk_actions;
}

add_filter('bulk_actions-users', 'User_Disable\register_enable_disable_bulk_actions');

// Handle Activate and Uninstall plugin actions
function uninstall_plugin()
{
	// Remove disabled user metadata from all users
	delete_metadata('user', -1, 'disabled', null, true);

	// Remove disable users capability
	$role = get_role('administrator');
	$role->remove_cap('disable_users');
}

register_uninstall_hook(__FILE__, 'User_Disable\uninstall_plugin');

function activate_plugin()
{
	// Give capability for disabling users to admins only
	$role = get_role('administrator');
	$role->add_cap('disable_users', true);
}

register_activation_hook(__FILE__, 'User_Disable\activate_plugin');
