<?php declare(strict_types=1);

use Chexwarrior\UserLoginDisableCmds;
use Chexwarrior\UserLoginDisablePlugin;
use PHPUnit\Framework\TestCase;

/**
 * The purpose of this file is to test the methods used within the
 * UserLoginDisableCmds->runUserQuery method that don't require the
 * WP API
 */
class RunUserQueryTest extends TestCase
{
	private ?UserLoginDisableCmds $cmdClass = null;

	protected function setUp(): void
	{
		$pluginClassStub = $this->createStub(UserLoginDisablePlugin::class);
		$this->cmdClass = new UserLoginDisableCmds($pluginClassStub);
	}

	/** @dataProvider validUserArgsProvider */
	public function testSplitIdsFromLoginEmailsWithValidInput(array $userArgs, array $expected) {
		$results = $this->cmdClass->splitIdsFromLoginEmails($userArgs);

		$this->assertEqualsCanonicalizing($results['user_ids'], $expected['user_ids']);
		$this->assertEqualsCanonicalizing($results['user_logins_emails'], $expected['user_logins_emails']);
	}

	// DATA PROVIDERS
	public function validUserArgsProvider(): array {
		$userArgs = [
			'13',
			'3',
			'5',
			'test@example.com',
			'roger3',
			'administrator',
		];

		$expected = [
			'user_ids' => ['13', '3', '5'],
			'user_logins_emails' => [
				'test@example.com',
				'roger3',
				'administrator'
			],
		];

		return [
			[$userArgs, $expected],
		];
	}
}
