<?php declare(strict_types=1);
/**
 * Plugin Name:     User Login Disable
 * Plugin URI:		https://github.com/ChexWarrior/wp-user-disable-login
 * Description:     Allows admins to enable and disable users login
 * Author:          Chexwarrior
 * Version:         0.1.0
 * Requires PHP: 	8.0
 *
 * @package         User_Login_Disable
 */

require dirname(__FILE__) . '/vendor/autoload.php';

 // Instantiate the class
$user_login_disable = Chexwarrior\UserLoginDisablePlugin::getInstance();

// Register activation and uninstall hooks
register_activation_hook(__FILE__, 'Chexwarrior\UserLoginDisablePlugin::activatePlugin');
register_uninstall_hook(__FILE__, 'Chexwarrior\UserLoginDisablePlugin::uninstallPlugin');

// Register our commands
if (defined('WP_CLI') && !empty(WP_CLI)) {
	$cli_commmands = new Chexwarrior\UserLoginDisableCmds(Chexwarrior\UserLoginDisablePlugin::getInstance());

	WP_CLI::add_command('user enable', [$cli_commmands, 'enableUsers']);
	WP_CLI::add_command('user disable', [$cli_commmands, 'disableUsers']);
}
