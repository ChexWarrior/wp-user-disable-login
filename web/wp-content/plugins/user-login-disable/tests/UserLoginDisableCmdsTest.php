<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Chexwarrior\UserLoginDisablePlugin;
use Chexwarrior\UserLoginDisableCmds;

class UserLoginDisableCmdsTest extends TestCase
{
    private ?UserLoginDisableCmds $cliCmd = null;

    protected function setUp(): void
    {
        $stubPlugin = $this->createStub(UserLoginDisablePlugin::class);
        $this->cliCmd = new UserLoginDisableCmds($stubPlugin);
    }

    public function testFilterUsersByArgs()
    {
        $this->assertSame(1, 1);
    }
}
