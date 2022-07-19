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
	 * [<user_ids>...]
	 * : A list of user ids for the users to be enabled
	 *
	 * [--all]
	 * : If this flag is included then all users in site will be enabled
	 *
	 */
	public function enable_users(array $user_args = [], array $assoc_args = []): void
	{
		['all' => $allFlag] = $assoc_args;
		$user_ids = $this->run_user_query(
			$allFlag === true,
			false,
			$user_args,
		);

		$count = $this->userLoginDisable->enable_disable_users('enable_user', $user_ids);

		WP_CLI::success("Enabled $count user(s)");
	}

	/**
	 * Builds user query for disabling or enabling users via WP_CLI
	 *
	 * @param bool $getAll
	 * @param bool $getEnabledUsers - If true we are getting users who are enabled, otherwise we're getting users who are disabled
	 * @param array $userArgs
	 */
    private function run_user_query(bool $getAll, bool $getEnabledUsers, array $userArgs = []): array
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

        if (!$getEnabledUsers) {
            $meta = [
                'meta_key' => 'disabled',
                'meta_value' => 'disabled',
            ];
        }

		if ($getAll) {
			$query_args = [
				'fields' => 'ID',
				'role__not_in' => 'administrator',
				'meta_query' => $meta,
			];

			return get_users($query_args);
		}

		['user_ids' => $ids, 'user_name_emails' => $names_emails] = $this->split_ids_from_names_emails($userArgs);

        $query_args = [
            'fields' => ['ID', 'user_login', 'user_email'],
            'role__not_in' => 'administrator',
            'meta_query' => $meta,
        ];

        $user_info = get_users($query_args);

		// Filter out users who don't match an ID, name or email param
		$user_info = array_filter($user_info, function($i) use ($ids, $names_emails) {
			if (in_array($i->ID, $ids)) {
				return true;
			}

			if (in_array($i->user_login, $names_emails)) {
				return true;
			}

			if (in_array($i->user_email, $names_emails)) {
				return true;
			}

			return false;
		});

		return array_map(fn($i) => $i->ID, $user_info);
    }

	/**
	 * Takes a list of user emails, user names and ids and returns
	 * an array with two values: one contains an array of the user ids passed in
	 * and another array with all the usernames and emails
	 * @param array $args
	 * @return array
	 */
	private function split_ids_from_names_emails(array $args): array
	{
		$non_ids = [];
		$ids = array_filter($args, function($i) use (&$non_ids) {
			if (is_numeric($i)) {
				return true;
			}

			$non_ids[] = $i;

			return false;
		});

		return [
			'user_ids' => $ids,
			'user_name_emails' => $non_ids,
		];
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
