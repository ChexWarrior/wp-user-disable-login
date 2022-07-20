<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Chexwarrior\UserLoginDisablePlugin;
use Chexwarrior\UserLoginDisableCmds;

/**
 * This is rather hacky but in order to test our wp-cli commands properly we'll
 * run them via shell commands in phpunit against the DDEV site and check
 * that they have the expected output (wp user meta get)
 */
class UserLoginDisableCmdsTest extends TestCase
{
    private ?UserLoginDisableCmds $cliCmd = null;
	private string $wpFullPath = '/var/www/html/web';

	protected function runWpCliCmd(string $cmd, array $params): ?string
	{
		$paramStr = implode(' ', $params);
		$output = shell_exec("wp --path={$this->wpFullPath} $cmd $paramStr");

		if (empty($output)) return null;

		return trim($output);
	}

	protected function isUserDisabled(string $userId): bool
	{
		return $this->runWpCliCmd('user meta get', [$userId, 'disabled']) === '1';
	}

	private function runPluginCliCmd(bool $disable, array $userId, bool $all): ?string
	{
		$params = $userId;

		if ($all) $params[] = '--all';

		$action = $disable ? 'disable' : 'enable';

		return $this->runWpCliCmd("user $action", $params);
	}

	protected function disableUsers(array $userIds, bool $all = false): void
	{
		$this->runPluginCliCmd(true, $userIds, $all);
	}

	protected function enableUsers(array $userIds, bool $all = false): void
	{
		$this->runPluginCliCmd(false, $userIds, $all);
	}

    public function testUserCanBeDisabledAndEnabled()
    {
        $this->disableUsers(['2']);
		$this->assertTrue($this->isUserDisabled('2'));

		$this->enableUsers(['2']);
		$this->assertFalse($this->isUserDisabled('2'));
    }
}
