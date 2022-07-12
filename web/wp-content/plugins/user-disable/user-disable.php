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
use WP_CLI;

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

// Update actual user disabled metadata
function update_disable_metadata($is_disabled, $user_id)
{
	// Logout target user when they are disabled
	if ($is_disabled) {
		$sessions = WP_Session_Tokens::get_instance($user_id);
		$sessions->destroy_all();
	}

	return update_user_meta(
		$user_id,
		'disabled',
		$is_disabled
	);
}

// Ensure meta data is updated and disabled users are logged out
function usermeta_form_field_disabled_update(int $user_id): int|bool
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	$disabled = $_POST['disabled'] === 'on';

	return update_disable_metadata($disabled, $user_id);
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
function enable_disable_users($action, $user_ids): int
{
	$count = 0;
	foreach ($user_ids as $id) {
		update_disable_metadata(
			$action === 'disable_user',
			$id
		);
		$count += 1;
	}

	return $count;
}

function register_enable_disable_bulk_actions($bulk_actions)
{
	$bulk_actions['disable_user'] = __('Disable User', 'disable_user');
	$bulk_actions['enable_user'] = __('Enable User', 'enable_user');

	return $bulk_actions;
}

function handle_enable_disable_bulk_actions($redirect_url, $action_name, $user_ids)
{
	if ($action_name === 'disable_user' || $action_name === 'enable_user') {
		$count = enable_disable_users($action_name, $user_ids);

		return add_query_arg($action_name, $count, $redirect_url);
	}

	return $redirect_url;
}

function enable_disable_bulk_notification() {
	if (!empty( $_REQUEST['disable_user'])) {
		$count = intval($_REQUEST['disable_user']);
		echo <<<HTML
		<div class="notice notice-info is-dismissible">
			<p>Disabled $count user(s).</p>
		</div>
		HTML;
	}

	if (!empty($_REQUEST['enable_user'])) {
		$count = intval($_REQUEST['enable_user']);
		echo <<<HTML
		<div class="notice notice-info is-dismissible">
			<p>Enabled $count user(s).</p>
		</div>
		HTML;
	}
}

add_action('admin_notices', 'User_Disable\enable_disable_bulk_notification');

add_filter('bulk_actions-users', 'User_Disable\register_enable_disable_bulk_actions');

add_filter('handle_bulk_actions-users', 'User_Disable\handle_enable_disable_bulk_actions', 10, 3);

// Add WP-CLI Commands for enabling/disabling users
function cli_enable_disable_users($args)
{
	echo 'User IDs: ' . implode(' ', $args);
}

if (defined('WP_CLI') && !empty(WP_CLI)) {
	WP_CLI::add_command('user enable', 'User_Disable\cli_enable_disable_users');
	WP_CLI::add_command('user disable', 'User_Disable\cli_enable_disable_users');
}

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
