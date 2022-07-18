<?php declare(strict_types=1);

class User_Login_Disable_CLI_Command
{
	private User_Login_Disable $userLoginDisable;

	public function __construct(User_Login_Disable $userLoginDisable)
	{
		$this->userLoginDisable = $userLoginDisable;
	}

	public function verify_user_ids(array $args): void
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

	public function enable_users(array $user_ids): void
	{
		$this->verify_user_ids($user_ids);
		$count = $this->userLoginDisable->enable_disable_users('enable_user', $user_ids);

		WP_CLI::success("Enabled $count user(s)");
	}

	public function disable_users(array $user_ids): void
	{
		$this->verify_user_ids($user_ids);
		$count = $this->userLoginDisable->enable_disable_users('disable_user', $user_ids);

		WP_CLI::success("Disabled $count user(s)");
	}
}

// Register our commands
$cli_commmands = new User_Login_Disable_CLI_Command(User_Login_Disable::get_instance());

WP_CLI::add_command('user enable', [$cli_commmands, 'enable_users']);
WP_CLI::add_command('user disable', [$cli_commmands, 'disable_users']);
