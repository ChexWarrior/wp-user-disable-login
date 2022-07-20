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

	// Test user information
	private array $admin1Info = ['id' => '1', 'name' => 'admin1',];
	private array $author1Info = ['id' => '2', 'name' => 'author1'];
	private array $admin2Info = ['id' => '3', 'name' => 'admin2'];
	private array $author2Info = ['id' => '4', 'name' => 'author2'];

	// Utility Methods
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

	private function runPluginCliCmd(bool $disable, array $userInfo, bool $all): ?string
	{
		$params = $userInfo;

		if ($all) $params[] = '--all';

		$action = $disable ? 'disable' : 'enable';

		return $this->runWpCliCmd("user $action", $params);
	}

	protected function disableUsers(array $userInfo, bool $all = false): void
	{
		$this->runPluginCliCmd(true, $userInfo, $all);
	}

	protected function enableUsers(array $userInfo, bool $all = false): void
	{
		$this->runPluginCliCmd(false, $userInfo, $all);
	}

	// TESTS
    public function testUserCanBeDisabledAndEnabled()
    {
        $this->disableUsers([$this->author1Info['id']]);
		$this->assertTrue($this->isUserDisabled($this->author1Info['id']));

		$this->enableUsers([$this->author1Info['id']]);
		$this->assertFalse($this->isUserDisabled($this->author1Info['id']));
    }

	public function testMultipleUsersCanBeDisabledAndEnabled()
	{
		$this->disableUsers([
			$this->author1Info['id'],
			$this->author2Info['id'],
		]);

		$this->assertTrue($this->isUserDisabled($this->author1Info['id']));
		$this->assertTrue($this->isUserDisabled($this->author2Info['id']));

		$this->enableUsers([
			$this->author1Info['id'],
			$this->author2Info['id'],
		]);

		$this->assertFalse($this->isUserDisabled($this->author1Info['id']));
		$this->assertFalse($this->isUserDisabled($this->author2Info['id']));
	}
}
