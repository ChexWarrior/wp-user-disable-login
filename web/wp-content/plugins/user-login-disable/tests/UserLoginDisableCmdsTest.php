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

	protected function runWpCliCmd(string $cmd, array $params)
	{
		$paramStr = implode(' ', $params);
		return trim(shell_exec("wp --path={$this->wpFullPath} $cmd $paramStr"));
	}

	protected function checkUserDisabled(string $userId)
	{
		return $this->runWpCliCmd('user meta get', [$userId, 'disabled']) === '1';
	}

	protected function checkUserEnabled(string $userId)
	{
		return $this->runWpCliCmd('user meta get', [$userId, 'disabled']) !== '1';
	}

	protected function disableEnableUser(bool $disable, string $userId, bool $all = false)
	{
		$params = [$userId];

		if ($all) $params[] = '--all';

		$action = $disable ? 'disable' : 'enable';

		return $this->runWpCliCmd("user $action", $params);
	}

    // protected function setUp(): void
    // {
    //     $stubPlugin = $this->createStub(UserLoginDisablePlugin::class);
    //     $this->cliCmd = new UserLoginDisableCmds($stubPlugin);
    // }

    public function testUserCanBeDisabled()
    {
        $this->disableEnableUser(true, '2');

		$this->assertTrue($this->checkUserDisabled('2'));
    }
}
