<?php

declare(strict_types=1);

/**
 * Plugin Name:     User Login Disable
 * Description:     Allows admins to enable and disable users login
 * Author:          Chexwarrior
 * Version:         0.1.0
 * Requires PHP: 	8.0
 *
 * @package         User_Login_Disable
 */

class User_Login_Disable
{
	/**
	 * This class is a singleton
	 */
	static ?User_Login_Disable $instance = null;

	function __construct()
	{
		// Setup hooks
		// Handles showing or hiding the field for enabling/disabling user login
		add_action('show_user_profile', [$this, 'usermeta_form_field_disabled']);
		add_action('edit_user_profile', [$this, 'usermeta_form_field_disabled']);
		add_action('personal_options_update', [$this, 'usermeta_form_field_disabled_update']);
		add_action('edit_user_profile_update', [$this, 'usermeta_form_field_disabled_update']);

		// Handles checking if a user is disabled when they're authenicated with WP
		add_filter('wp_authenticate_user', [$this, 'check_if_user_disabled'], 10, 2);

		// Checks if a user is disabled when using an app password with API
		add_action('wp_authenticate_application_password_errors', [$this, 'check_if_user_disabled_for_api'], 10, 4);

		// Add User Disabled column to admin user's list
		add_filter('manage_users_columns', [$this, 'add_user_disabled_column']);
		add_filter('manage_users_custom_column', [$this, 'show_user_disabled_column'], 10, 3);

		// Update Admin Notices for plugin functionality
		add_action('admin_notices', [$this, 'enable_disable_bulk_notification']);

		// Handle Bulk Actions for enabling/disabling users
		add_filter('bulk_actions-users', [$this, 'register_enable_disable_bulk_actions']);
		add_filter('handle_bulk_actions-users', [$this, 'handle_enable_disable_bulk_actions'], 10, 3);

		register_activation_hook(__FILE__, 'User_Login_Disable::activate_plugin');
		register_uninstall_hook(__FILE__, 'User_Login_Disable::uninstall_plugin');

		// Setup commands for WP-CLI
		if (defined('WP_CLI') && !empty(WP_CLI)) {
			WP_CLI::add_command('user enable', [$this, 'cli_enable_users']);
			WP_CLI::add_command('user disable', [$this, 'cli_disable_users']);
		}
	}

	public static function get_instance()
	{
		if (!self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function usermeta_form_field_disabled(WP_User $user)
	{
		if (!in_array('administrator', $user->roles) && current_user_can('disable_users')) : ?>
			<h3>Disable User Login</h3>
			<table class="form-table">
				<tr>
					<th>
						<label for="disabled">Is Disabled</label>
					</th>
					<td>
						<input type="checkbox" id="disabled" name="disabled" <?php
						echo esc_attr(get_user_meta($user->ID, 'disabled', true))
							? 'checked' : '' ?>
							title="If checked user will not be able to login" required>
						<p class="description">
							If checked user will not be able to login.
						</p>
					</td>
				</tr>
			</table>
		<?php endif;
	}

	// Update actual user disabled metadata
	public function update_disable_metadata($is_disabled, $user_id)
	{
		// Administrators cannot be disabled
		$user = get_userdata($user_id);
		if (in_array('administrator', $user->roles)) {
			return false;
		}

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
	public function usermeta_form_field_disabled_update(int $user_id): int|bool
	{
		if (!current_user_can('edit_user', $user_id)) {
			return false;
		}

		$disabled = $_POST['disabled'] === 'on';

		return $this->update_disable_metadata($disabled, $user_id);
	}

	// Ensure we check disabled meta when a user logs in
	public function check_if_user_disabled(WP_User|WP_Error $user, string $password): WP_User|WP_Error
	{
		$disabled = get_user_meta($user->ID, 'disabled', true) === "1";

		if ($disabled) {
			return new WP_Error('user_disabled', 'User is disabled', $user->ID);
		}

		return $user;
	}

	public function check_if_user_disabled_for_api(WP_Error $error, WP_User $user, array $item, string $password)
	{
		$disabled_user_error = $this->check_if_user_disabled($user, $password);

		if ($disabled_user_error instanceof WP_Error) {
			$error->add(
				$disabled_user_error->get_error_code(),
				$disabled_user_error->get_error_message()
			);
		}
	}

	public function add_user_disabled_column(array $columns): array
	{
		$check_column = $columns['cb'];
		unset($columns['cb']);
		return array_merge(
			[
				'cb' => $check_column,
				'user_disabled' => 'User Disabled'
			],
			$columns
		);
	}

	public function show_user_disabled_column($value, $column_name, $user_id)
	{
		if ($column_name === 'user_disabled') {
			$user_data = get_userdata($user_id);
			return $user_data->get('disabled') === "1"
			? '<strong class="file-error">Disabled</strong>' : '';
		}

		return $value;
	}

	public function enable_disable_users($action, $user_ids): int
	{
		$count = 0;
		foreach ($user_ids as $id) {
			$this->update_disable_metadata(
				$action === 'disable_user',
				$id
			);
			$count += 1;
		}

		return $count;
	}


	public function register_enable_disable_bulk_actions($bulk_actions)
	{
		$bulk_actions['disable_user'] = __('Disable User', 'disable_user');
		$bulk_actions['enable_user'] = __('Enable User', 'enable_user');

		return $bulk_actions;
	}

	public function handle_enable_disable_bulk_actions($redirect_url, $action_name, $user_ids)
	{
		if ($action_name === 'disable_user' || $action_name === 'enable_user') {
			$count = $this->enable_disable_users($action_name, $user_ids);

			return add_query_arg($action_name, $count, $redirect_url);
		}

		return $redirect_url;
	}

	public function enable_disable_bulk_notification()
	{
		if (!empty($_REQUEST['disable_user'])) {
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

	// Add WP-CLI Commands for enabling/disabling users
	public function cli_disable_users($user_ids)
	{
		$this->cli_verify_user_ids($user_ids);
		$count = $this->enable_disable_users('disable_user', $user_ids);

		WP_CLI::success("Disabled $count user(s)");
	}

	public function cli_enable_users($user_ids)
	{
		$this->cli_verify_user_ids($user_ids);
		$count = $this->enable_disable_users('enable_user', $user_ids);

		WP_CLI::success("Enabled $count user(s)");
	}

	public function cli_verify_user_ids($args)
	{
		if (!is_array($args) || empty($args)) {
			WP_CLI::error("Must pass array of user ids!");
		}

		foreach ($args as $arg) {
			if (intval($arg) === 0 || $arg < 1) {
				WP_CLI::error('User ids must be positive integers!');
			}
		}
	}

	public static function uninstall_plugin()
	{
		// Remove disabled user metadata from all users
		delete_metadata('user', -1, 'disabled', null, true);

		// Remove disable users capability
		$role = get_role('administrator');
		$role->remove_cap('disable_users');
	}

	public static function activate_plugin()
	{
		// Give capability for disabling users to admins only
		$role = get_role('administrator');
		$role->add_cap('disable_users', true);
	}
}

// Instantiate the class
$user_login_disable = User_Login_Disable::get_instance();
