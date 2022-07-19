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

	/**
	 * Enables users
	 *
	 * ## OPTIONS
	 *
	 * [user_ids]
	 * : A list of user ids for the users to be enabled
	 *
	 * [--all]
	 * : If this flag is included then all users in site will be enabled
	 *
	 */
	public function enable_users(array $user_ids = [], array $assoc_args = []): void
	{
        /**
         * Check if the --all flag was passed, if so we grab all user ids and
         * enable them
         *
         * If no all flag then determine grab passed user identifiers and delete each (can be username, useremail or id)
         */
		['all' => $allFlag] = $assoc_args;

		if ($allFlag) {
			// Get all user ids
            $user_ids = $this->run_user_query(true, false);
		} else {
			$this->verify_user_ids($user_ids);
		}

		$count = $this->userLoginDisable->enable_disable_users('enable_user', $user_ids);

		WP_CLI::success("Enabled $count user(s)");
	}

    /**
     * Builds user query for disabling or enabling users via WP_CLI
     *
     * @param bool $getAll
     * @param bool $enabled - If true we are getting users who are enabled, otherwise we're getting users who are disabled
     * @param array $userArgs
     */
    private function run_user_query(bool $getAll, bool $enabled, array $userArgs = []): array
    {
        // Default to getting enabled users
        $meta = [
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'disabled',
                    'value' => '',
                ],
                [
                    'key' => 'disabled',
                    'compare' => 'NOT EXISTS',
                ]
            ],
        ];

        if (!$enabled) {
            $meta = [
                'meta_key' => 'disabled',
                'meta_value' => 'disabled',
            ];
        }

        $query_args = [
            'fields' => 'ID',
            'role__not_in' => 'administrator',
            'meta_query' => $meta,
        ];

        return get_users($query_args);
    }

	/**
	 * Disable users
	 *
	 * ## OPTIONS
	 *
	 * <user_ids>
	 * : A list of user ids for the users to be disabled
	 *
	 * [--all]
	 * : If this flag is included then all non-admin users in site will be disabled
	 *
	 */
	public function disable_users(array $user_ids, array $assoc_args): void
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
