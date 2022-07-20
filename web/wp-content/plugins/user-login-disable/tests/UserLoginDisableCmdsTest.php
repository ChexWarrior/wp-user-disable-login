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

	private function runPluginCliCmd(bool $disable, string $userId, bool $all)
	{
		$params = [$userId];

		if ($all) $params[] = '--all';

		$action = $disable ? 'disable' : 'enable';

		return $this->runWpCliCmd("user $action", $params);
	}

	protected function disableUser(string $userId, bool $all = false)
	{
		return $this->runPluginCliCmd(true, $userId, $all);
	}

	protected function enableUser(string $userId, bool $all = false)
	{
		return $this->runPluginCliCmd(false, $userId, $all);
	}

    // protected function setUp(): void
    // {
    //     $stubPlugin = $this->createStub(UserLoginDisablePlugin::class);
    //     $this->cliCmd = new UserLoginDisableCmds($stubPlugin);
    // }

    public function testUserCanBeDisabledAndEnabled()
    {
        $this->disableUser('2');
		$this->assertTrue($this->isUserDisabled('2'));

		$this->enableUser('2');
		$this->assertFalse($this->isUserDisabled('2'));
    }
}
