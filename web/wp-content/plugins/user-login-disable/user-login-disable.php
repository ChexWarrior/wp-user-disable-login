<?php declare(strict_types=1);
/**
 * Plugin Name:     User Login Disable
 * Description:     Allows admins to enable and disable users login
 * Author:          Chexwarrior
 * Version:         0.1.0
 * Requires PHP: 	8.0
 *
 * @package         User_Login_Disable
 */

require dirname(__FILE__) . '/include/UserLoginDisablePlugin.php';

 // Instantiate the class
$user_login_disable = UserLoginDisablePlugin::getInstance();

// Register activation and uninstall hooks
register_activation_hook(__FILE__, 'UserLoginDisablePlugin::activatePlugin');
register_uninstall_hook(__FILE__, 'UserLoginDisablePlugin::uninstallPlugin');

if (defined('WP_CLI') && !empty(WP_CLI)) {
	require dirname(__FILE__) . '/include/UserLoginDisableCmds.php';

	// Register our commands
	$cli_commmands = new UserLoginDisableCmds(UserLoginDisablePlugin::getInstance());

	WP_CLI::add_command('user enable', [$cli_commmands, 'enableUsers']);
	WP_CLI::add_command('user disable', [$cli_commmands, 'disableUsers']);
}
